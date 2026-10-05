<?php

namespace App\Services;

use App\Models\Patient;
use App\Support\Phone;

class PatientService
{
    private const FILLABLE_IF_EMPTY = ['first_name', 'last_name', 'email', 'reason', 'preferred_modality'];

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
}
