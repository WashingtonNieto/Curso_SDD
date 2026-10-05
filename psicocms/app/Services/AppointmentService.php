<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PatientService $patients,
    ) {}

    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            $appointment = new Appointment;
            $this->fill($appointment, $data);
            $appointment->save();

            return $appointment;
        });
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            $this->fill($appointment, $data);
            $appointment->save();

            return $appointment;
        });
    }

    public function changeStatus(Appointment $appointment, string $status): Appointment
    {
        return DB::transaction(function () use ($appointment, $status) {
            $appointment->status = $status;

            if ($appointment->isDirty('status') && $status !== 'cancelada') {
                $this->lockDay($appointment->starts_at);
                $this->guardOverlap($appointment);
            }

            $appointment->save();

            return $appointment;
        });
    }

    public function move(Appointment $appointment, CarbonImmutable $start): Appointment
    {
        if ($appointment->status === 'cancelada') {
            throw ValidationException::withMessages(['start' => 'Las citas canceladas no se pueden mover.']);
        }

        if ($start->lt(CarbonImmutable::now())) {
            throw ValidationException::withMessages(['start' => 'No puedes mover una cita a una fecha u hora pasada.']);
        }

        return DB::transaction(function () use ($appointment, $start) {
            $minutes = (int) $appointment->starts_at->diffInMinutes($appointment->ends_at);
            $appointment->starts_at = $start;
            $appointment->ends_at = $start->addMinutes($minutes);

            $this->lockDay($start);
            $this->guardOverlap($appointment);

            $appointment->save();

            return $appointment;
        });
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    public function defaultPrice(string $modality): ?string
    {
        return Plan::query()->active()->forModality($modality)->orderByRaw("CASE WHEN modality = ? THEN 0 ELSE 1 END", [$modality])->ordered()->value('price');
    }

    private function fill(Appointment $appointment, array $data): void
    {
        $patient = $this->patients->findOrCreateByPhone([
            'phone' => $data['phone'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'reason' => $data['reason'] ?? null,
            'preferred_modality' => $data['modality'],
        ], ($data['source'] ?? 'otro') === 'web' ? 'web' : 'manual');

        $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'])->startOfMinute();
        $scheduleChanged = ! $appointment->exists
            || ! $appointment->starts_at->equalTo($start)
            || $appointment->modality !== $data['modality'];

        $appointment->fill([
            'patient_id' => $patient->id,
            'modality' => $data['modality'],
            'status' => $data['status'],
            'source' => $data['source'],
            'reason' => $data['reason'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'price' => ($data['price'] ?? null) !== null ? $data['price'] : $this->defaultPrice($data['modality']),
        ]);

        if ($scheduleChanged) {
            $setting = $this->availability->setting($data['modality']);
            $appointment->starts_at = $start;
            $appointment->ends_at = $start->addMinutes($setting->session_duration);
            $appointment->break_minutes = $setting->effectiveBreak();
        }

        if ($appointment->status === 'cancelada') {
            return;
        }

        $this->lockDay($appointment->starts_at);
        $this->guardOverlap($appointment);

        if ($scheduleChanged && empty($data['custom_time']) && ! $this->availability->isBookable($appointment->modality, $start, $appointment->id, true)) {
            throw ValidationException::withMessages([
                'slot_time' => 'Ese hueco ya no está disponible. Elige otro horario o marca “Hora personalizada”.',
            ]);
        }
    }

    private function guardOverlap(Appointment $appointment): void
    {
        $start = CarbonImmutable::parse($appointment->starts_at);
        $end = CarbonImmutable::parse($appointment->ends_at)->addMinutes($appointment->break_minutes);
        $conflict = $this->availability->overlaps($start, $end, $appointment->id);

        if ($conflict) {
            throw ValidationException::withMessages([
                'slot_time' => sprintf(
                    'Ese horario se solapa con la cita de %s a las %s.',
                    $conflict->patient?->full_name ?: 'otro paciente',
                    $conflict->starts_at->format('H:i')
                ),
            ]);
        }
    }

    private function lockDay($date): void
    {
        $day = CarbonImmutable::parse($date)->startOfDay();

        Appointment::query()
            ->where('starts_at', '>=', $day->subDay())
            ->where('starts_at', '<', $day->addDays(2))
            ->lockForUpdate()
            ->get(['id']);
    }
}
