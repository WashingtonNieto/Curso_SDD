<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\AvailabilitySlot;
use App\Models\VacationPeriod;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    public const MAX_BREAK_MINUTES = 60;

    public const SCHEDULE_FIELDS = ['session_duration', 'break_enabled', 'break_minutes', 'day_start', 'day_end'];

    public function __construct(private readonly SettingsService $settings) {}

    public function setting(string $modality): AvailabilitySetting
    {
        return AvailabilitySetting::forModality($modality);
    }

    public function grid(string $modality): array
    {
        $setting = $this->setting($modality);

        return self::times($setting->session_duration, $setting->effectiveBreak(), $setting->day_start, $setting->day_end);
    }

    public static function times(int $duration, int $break, string $dayStart, string $dayEnd): array
    {
        if ($duration <= 0) {
            return [];
        }

        $cursor = Carbon::createFromFormat('H:i', substr($dayStart, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($dayEnd, 0, 5));
        $step = $duration + max(0, $break);
        $times = [];

        while ($cursor->copy()->addMinutes($duration)->lte($end)) {
            $times[] = $cursor->format('H:i');
            $cursor->addMinutes($step);
        }

        return $times;
    }

    public function weeklySlots(string $modality): array
    {
        $week = array_fill_keys(range(1, 7), []);

        AvailabilitySlot::modality($modality)->orderBy('start_time')->get()->each(function (AvailabilitySlot $slot) use (&$week) {
            $week[$slot->weekday][] = substr($slot->start_time, 0, 5);
        });

        return $week;
    }

    public function weekdaysWithSlots(string $modality): array
    {
        return AvailabilitySlot::modality($modality)->distinct()->orderBy('weekday')->pluck('weekday')->all();
    }

    public function weeklyRanges(string $modality): array
    {
        $setting = $this->setting($modality);
        $step = $setting->step();
        $ranges = [];

        foreach ($this->weeklySlots($modality) as $weekday => $times) {
            $ranges[$weekday] = [];
            $blockStart = $previous = null;

            foreach ($times as $time) {
                $minutes = $this->toMinutes($time);

                if ($previous !== null && $minutes !== $previous + $step) {
                    $ranges[$weekday][] = [$this->toTime($blockStart), $this->toTime($previous + $setting->session_duration)];
                    $blockStart = null;
                }

                $blockStart ??= $minutes;
                $previous = $minutes;
            }

            if ($blockStart !== null) {
                $ranges[$weekday][] = [$this->toTime($blockStart), $this->toTime($previous + $setting->session_duration)];
            }
        }

        return $ranges;
    }

    public function fillWeekdays(string $modality, array $weekdays): int
    {
        $rows = [];

        foreach ($this->grid($modality) as $time) {
            foreach ($weekdays as $weekday) {
                $rows[] = ['weekday' => (int) $weekday, 'time' => $time];
            }
        }

        return $this->saveWeeklySlots($modality, $rows);
    }

    public function saveWeeklySlots(string $modality, array $slots): int
    {
        $grid = $this->grid($modality);

        $rows = collect($slots)
            ->filter(fn ($slot) => in_array((int) ($slot['weekday'] ?? 0), range(1, 7), true) && in_array($slot['time'] ?? null, $grid, true))
            ->map(fn ($slot) => ['modality' => $modality, 'weekday' => (int) $slot['weekday'], 'start_time' => $slot['time'].':00'])
            ->unique(fn ($row) => $row['weekday'].'-'.$row['start_time'])
            ->values()
            ->all();

        DB::transaction(function () use ($modality, $rows) {
            AvailabilitySlot::modality($modality)->delete();

            if ($rows !== []) {
                AvailabilitySlot::insert($rows);
            }

            $this->setting($modality)->update(['needs_review' => false]);
        });

        return count($rows);
    }

    public function updateSchedule(string $modality, array $data): bool
    {
        $setting = $this->setting($modality);
        $setting->fill([
            'session_duration' => (int) $data['session_duration'],
            'break_enabled' => (bool) ($data['break_enabled'] ?? false),
            'break_minutes' => (int) ($data['break_minutes'] ?? $setting->break_minutes),
            'day_start' => substr($data['day_start'], 0, 5).':00',
            'day_end' => substr($data['day_end'], 0, 5).':00',
        ]);

        $changed = $setting->isDirty(self::SCHEDULE_FIELDS);

        DB::transaction(function () use ($setting, $modality, $changed) {
            $setting->save();

            if (! $changed) {
                return;
            }

            $validTimes = array_map(fn ($time) => $time.':00', $this->grid($modality));
            AvailabilitySlot::modality($modality)->whereNotIn('start_time', $validTimes)->delete();
            AvailabilitySetting::query()->update(['needs_review' => true]);
        });

        return $changed;
    }

    public function modalitiesNeedingReview(): array
    {
        return AvailabilitySetting::query()->where('needs_review', true)->orderBy('modality')->pluck('modality')->all();
    }

    public function isVacationMode(): bool
    {
        return (bool) $this->settings->get('booking.vacation_mode', false);
    }

    public function setVacationMode(bool $enabled): void
    {
        $this->settings->set('booking.vacation_mode', $enabled);
    }

    public function blockReason(CarbonInterface|string $date): ?string
    {
        if ($this->isVacationMode()) {
            return 'vacation_mode';
        }

        return VacationPeriod::covering(CarbonImmutable::parse($date))->exists() ? 'vacation_period' : null;
    }

    public function slotsForDate(string $modality, CarbonInterface|string $date, ?int $ignoreAppointmentId = null, bool $forPanel = false): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();

        if ($this->blockReason($day) !== null || (! $forPanel && $day->gt($this->bookingHorizon()))) {
            return [];
        }

        $setting = $this->setting($modality);
        $length = $setting->session_duration + $setting->effectiveBreak();
        $earliest = $forPanel ? CarbonImmutable::now() : CarbonImmutable::now()->addHours(config('psicocms.booking.min_notice_hours', 2));
        $busy = $this->busyIntervals($day->subDay(), $day->addDays(2), $ignoreAppointmentId);

        return collect($this->weeklySlots($modality)[$day->dayOfWeekIso])
            ->filter(function (string $time) use ($day, $length, $earliest, $busy) {
                $start = $day->setTimeFromTimeString($time);

                return $start->gte($earliest) && ! $this->collides($busy, $start, $start->addMinutes($length));
            })
            ->values()
            ->all();
    }

    public function availableDates(string $modality, string $month, bool $forPanel = false): array
    {
        $first = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $today = CarbonImmutable::today();
        $dates = [];

        for ($day = $first; $day->month === $first->month; $day = $day->addDay()) {
            $dates[$day->toDateString()] = $day->gte($today) && $this->slotsForDate($modality, $day, null, $forPanel) !== [];
        }

        return $dates;
    }

    public function isBookable(string $modality, CarbonInterface $start, ?int $ignoreAppointmentId = null, bool $forPanel = false): bool
    {
        return in_array($start->format('H:i'), $this->slotsForDate($modality, $start, $ignoreAppointmentId, $forPanel), true);
    }

    public function overlaps(CarbonInterface $start, CarbonInterface $end, ?int $ignoreAppointmentId = null): ?Appointment
    {
        return Appointment::query()
            ->with('patient')
            ->notCancelled()
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', CarbonImmutable::parse($start)->subMinutes(self::MAX_BREAK_MINUTES))
            ->orderBy('starts_at')
            ->get()
            ->first(fn (Appointment $appointment) => $appointment->ends_at->copy()->addMinutes($appointment->break_minutes)->gt($start));
    }

    public function bookingHorizon(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays(config('psicocms.booking.max_days_ahead', 90))->endOfDay();
    }

    private function busyIntervals(CarbonInterface $from, CarbonInterface $to, ?int $ignoreAppointmentId): Collection
    {
        return Appointment::query()
            ->notCancelled()
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->get(['starts_at', 'ends_at', 'break_minutes'])
            ->map(fn (Appointment $appointment) => [
                CarbonImmutable::parse($appointment->starts_at),
                CarbonImmutable::parse($appointment->ends_at)->addMinutes($appointment->break_minutes),
            ]);
    }

    private function collides(Collection $busy, CarbonInterface $start, CarbonInterface $end): bool
    {
        return $busy->contains(fn (array $interval) => $start->lt($interval[1]) && $end->gt($interval[0]));
    }

    private function toMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }

    private function toTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
