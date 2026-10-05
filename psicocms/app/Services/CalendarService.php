<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\VacationPeriod;
use Carbon\CarbonImmutable;

class CalendarService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function events(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $appointments = Appointment::with('patient')
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Appointment $appointment) => $this->appointmentEvent($appointment));

        $vacations = VacationPeriod::overlapping($from->toDateString(), $to->toDateString())
            ->get()
            ->map(fn (VacationPeriod $period) => [
                'id' => 'vacation-'.$period->id,
                'title' => $period->note ?: 'Vacaciones',
                'start' => $period->start_date->toDateString(),
                'end' => $period->end_date->copy()->addDay()->toDateString(),
                'allDay' => true,
                'display' => 'background',
                'classNames' => ['fc-vacation'],
                'extendedProps' => ['type' => 'vacation'],
            ]);

        return $appointments->concat($vacations)->values()->all();
    }

    public function appointmentEvent(Appointment $appointment): array
    {
        $patient = $appointment->patient;
        $minutes = (int) $appointment->starts_at->diffInMinutes($appointment->ends_at);
        $cancelled = $appointment->status === 'cancelada';

        return [
            'id' => (string) $appointment->id,
            'title' => $patient?->full_name ?: 'Paciente',
            'start' => $appointment->starts_at->format('Y-m-d\TH:i:s'),
            'end' => $appointment->ends_at->format('Y-m-d\TH:i:s'),
            'editable' => ! $cancelled && $appointment->starts_at->isFuture(),
            'classNames' => array_values(array_filter([
                'fc-event--'.$appointment->modality,
                'fc-event--status-'.$appointment->status,
                $cancelled ? 'is-cancelled' : null,
            ])),
            'extendedProps' => [
                'type' => 'appointment',
                'modality' => $appointment->modality,
                'modalityLabel' => Appointment::MODALITIES[$appointment->modality] ?? $appointment->modality,
                'status' => $appointment->status,
                'statusLabel' => Appointment::STATUSES[$appointment->status] ?? $appointment->status,
                'source' => Appointment::SOURCES[$appointment->source] ?? $appointment->source,
                'phone' => $patient?->phone,
                'email' => $patient?->email,
                'initials' => $patient?->initials,
                'reason' => $appointment->reason,
                'price' => $appointment->price !== null ? money($appointment->price) : null,
                'duration' => $minutes,
                'breakMinutes' => $appointment->break_minutes,
                'dateLabel' => ucfirst($appointment->starts_at->translatedFormat('l j \d\e F \d\e Y')),
                'timeLabel' => $appointment->starts_at->format('H:i').' – '.$appointment->ends_at->format('H:i'),
                'urls' => [
                    'edit' => route('panel.appointments.edit', $appointment),
                    'status' => route('panel.appointments.status', $appointment),
                    'move' => route('panel.appointments.move', $appointment),
                    'delete' => route('panel.appointments.destroy', $appointment),
                    'patient' => route('panel.patients.index', ['q' => $patient?->phone]),
                ],
            ],
        ];
    }

    public function businessHours(): array
    {
        $hours = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            foreach ($this->availability->weeklyRanges($modality) as $weekday => $ranges) {
                foreach ($ranges as [$start, $end]) {
                    $hours[] = ['daysOfWeek' => [$weekday % 7], 'startTime' => $start, 'endTime' => $end];
                }
            }
        }

        return $hours;
    }

    public function scrollTime(): string
    {
        $earliest = AvailabilitySetting::query()->min('day_start');

        return $earliest ? substr($earliest, 0, 5).':00' : '08:00:00';
    }
}
