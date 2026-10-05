<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\BlogPost;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardStatsService
{
    public const WEEKDAYS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    public function __construct(private readonly AvailabilityService $availability) {}

    public function summary(): array
    {
        $today = CarbonImmutable::today();

        return [
            'appointmentsToday' => Appointment::notCancelled()->onDate($today->toDateString())->count(),
            'activePatients' => Patient::active()->count(),
            'publishedPosts' => BlogPost::published()->count(),
            'monthIncome' => (float) Appointment::notCancelled()
                ->whereBetween('starts_at', [$today->startOfMonth(), $today->endOfMonth()])
                ->sum('price'),
        ];
    }

    public function todayAppointments(): Collection
    {
        return Appointment::with('patient')
            ->notCancelled()
            ->onDate(CarbonImmutable::today()->toDateString())
            ->orderBy('starts_at')
            ->get();
    }

    public function newWebBookings(int $limit = 5): Collection
    {
        return Appointment::with('patient')
            ->where('source', 'web')
            ->whereNull('seen_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function weeklyAvailability(): array
    {
        $ranges = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $ranges[$modality] = $this->availability->weeklyRanges($modality);
        }

        $week = [];

        foreach (self::WEEKDAYS as $number => $name) {
            $week[$number] = [
                'name' => $name,
                'online' => $ranges['online'][$number] ?? [],
                'presencial' => $ranges['presencial'][$number] ?? [],
            ];
        }

        return $week;
    }

    public function lastWeeksActivity(int $weeks = 8): array
    {
        $currentWeek = CarbonImmutable::today()->startOfWeek();
        $from = $currentWeek->subWeeks($weeks - 1);

        $counts = Appointment::notCancelled()
            ->whereBetween('starts_at', [$from, $currentWeek->endOfWeek()])
            ->get(['starts_at'])
            ->countBy(fn (Appointment $appointment) => CarbonImmutable::parse($appointment->starts_at)->startOfWeek()->toDateString());

        $max = max(1, (int) $counts->max());
        $result = [];

        for ($week = $from; $week->lte($currentWeek); $week = $week->addWeek()) {
            $count = (int) ($counts[$week->toDateString()] ?? 0);
            $result[] = [
                'label' => $week->translatedFormat('j M'),
                'range' => $week->translatedFormat('j M').' – '.$week->endOfWeek()->translatedFormat('j M'),
                'count' => $count,
                'percent' => (int) round($count / $max * 100),
                'current' => $week->equalTo($currentWeek),
            ];
        }

        return $result;
    }
}
