<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class PatientService
{
    private const FILLABLE_IF_EMPTY = ['first_name', 'last_name', 'email', 'reason', 'preferred_modality'];

    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    public function findOrCreateByPhone(array $data, string $source = 'manual'): Patient
    {
        $phone = Phone::normalize($data['phone'] ?? null);
        $patient = Patient::withTrashed()->where('phone', $phone)->first();

        if (! $patient) {
            return Patient::create(array_merge(
                array_intersect_key($data, array_flip(self::FILLABLE_IF_EMPTY)),
                ['phone' => $phone, 'source' => $source, 'status' => 'activo']
            ));
        }

        if ($patient->trashed()) {
            $patient->restore();
        }

        foreach (self::FILLABLE_IF_EMPTY as $field) {
            if (blank($patient->{$field}) && filled($data[$field] ?? null)) {
                $patient->{$field} = $data[$field];
            }
        }

        $patient->save();

        return $patient;
    }

    /**
     * Crea la ficha o, si el teléfono pertenecía a un paciente eliminado, la recupera con los datos nuevos.
     *
     * @return array{0: Patient, 1: bool}
     */
    public function createManual(array $data): array
    {
        $patient = Patient::onlyTrashed()->where('phone', $data['phone'])->first();
        $restored = $patient !== null;

        if ($restored) {
            $patient->restore();
        } else {
            $patient = new Patient(['source' => 'manual']);
        }

        $this->save($patient, $data);

        return [$patient, $restored];
    }

    public function save(Patient $patient, array $data): Patient
    {
        $data['notes'] = $this->sanitizer->clean($data['notes'] ?? null);
        $patient->fill($data)->save();

        return $patient;
    }

    public function stats(): array
    {
        $month = CarbonImmutable::now();

        return [
            'total' => Patient::count(),
            'activeThisMonth' => Patient::whereHas('appointments', fn (Builder $query) => $query
                ->notCancelled()
                ->whereBetween('starts_at', [$month->startOfMonth(), $month->endOfMonth()]))
                ->count(),
            'today' => Appointment::notCancelled()->onDate(today()->toDateString())->count(),
        ];
    }

    public function listQuery(array $filters): Builder
    {
        $now = now();
        $notCancelled = fn () => Appointment::select('starts_at')
            ->whereColumn('patient_id', 'patients.id')
            ->where('status', '!=', 'cancelada');

        return Patient::query()
            ->select('patients.*')
            ->addSelect([
                'last_appointment_at' => $notCancelled()->where('starts_at', '<', $now)->orderByDesc('starts_at')->limit(1),
                'next_appointment_at' => $notCancelled()->where('starts_at', '>=', $now)->orderBy('starts_at')->limit(1),
            ])
            ->withCasts(['last_appointment_at' => 'datetime', 'next_appointment_at' => 'datetime'])
            ->withCount(['appointments', 'clinicalEntries'])
            ->search($filters['q'] ?? null)
            ->when($filters['estado'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['modalidad'] ?? null, fn (Builder $query, $modality) => $query->where('preferred_modality', $modality))
            ->when($filters['genero'] ?? null, fn (Builder $query, $gender) => $query->where('gender', $gender))
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    public function row(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->full_name,
            'initials' => $patient->initials,
            'phone' => $patient->phone,
            'subtitle' => $patient->therapy_type ?: $patient->reason,
            'status' => $patient->status,
            'statusLabel' => Patient::STATUSES[$patient->status] ?? $patient->status,
            'modality' => $patient->preferred_modality,
            'modalityLabel' => Appointment::MODALITIES[$patient->preferred_modality] ?? null,
            'lastAppointment' => $patient->last_appointment_at?->translatedFormat('j M Y'),
            'nextAppointment' => $patient->next_appointment_at ? [
                'date' => $patient->next_appointment_at->translatedFormat('j M Y'),
                'time' => $patient->next_appointment_at->format('H:i'),
            ] : null,
            'deleteMessage' => $this->deleteMessage($patient),
            'urls' => [
                'show' => route('panel.patients.show', $patient),
                'edit' => route('panel.patients.edit', $patient),
                'destroy' => route('panel.patients.destroy', $patient),
                'appointment' => route('panel.appointments.create', ['paciente' => $patient->id]),
            ],
        ];
    }

    public function deleteMessage(Patient $patient): string
    {
        $appointments = $patient->appointments_count ?? $patient->appointments()->count();
        $entries = $patient->clinical_entries_count ?? $patient->clinicalEntries()->count();

        $parts = [];
        $parts[] = $appointments === 1 ? '1 cita' : $appointments.' citas';
        $parts[] = $entries === 1 ? '1 nota de historia clínica' : $entries.' notas de historia clínica';

        return 'La ficha de '.$patient->full_name.' dejará de aparecer en tu panel. Tiene '.implode(' y ', $parts)
            .', que se conservan. Si vuelve a pedir cita con el mismo teléfono, su ficha se recuperará.';
    }

    public function summary(Patient $patient): array
    {
        $appointments = $patient->appointments()->notCancelled();

        return [
            'sessions' => (clone $appointments)->where('starts_at', '<', now())->whereIn('status', ['confirmada', 'completada'])->count(),
            'last' => (clone $appointments)->where('starts_at', '<', now())->orderByDesc('starts_at')->first(),
            'next' => (clone $appointments)->where('starts_at', '>=', now())->orderBy('starts_at')->first(),
        ];
    }
}
