<?php

namespace App\Http\Requests\Panel;

use App\Models\Appointment;
use App\Models\Patient;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PatientRequest extends FormRequest
{
    private const PLAIN_FIELDS = [
        'first_name', 'last_name', 'email', 'dni', 'address', 'city', 'postal_code', 'occupation',
        'emergency_contact_name', 'therapy_type', 'reason',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'phone' => Phone::normalize($this->input('phone')),
            'emergency_contact_phone' => Phone::normalize($this->input('emergency_contact_phone')),
        ];

        foreach (self::PLAIN_FIELDS as $field) {
            $value = trim(strip_tags((string) $this->input($field)));
            $data[$field] = $value === '' ? null : $value;
        }

        if ($data['email'] !== null) {
            $data['email'] = mb_strtolower($data['email']);
        }

        if ($data['dni'] !== null) {
            $data['dni'] = mb_strtoupper(str_replace(' ', '', $data['dni']));
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'regex:'.Phone::PATTERN],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date', 'after:1900-01-01', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(array_keys(Patient::GENDERS))],
            'dni' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'regex:'.Phone::PATTERN],
            'preferred_modality' => ['nullable', Rule::in(array_keys(Appointment::MODALITIES))],
            'status' => ['required', Rule::in(array_keys(Patient::STATUSES))],
            'therapy_type' => ['nullable', 'string', 'max:150'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:100000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('phone')) {
                    return;
                }

                $current = $this->route('patient');
                $existing = Patient::withTrashed()
                    ->where('phone', $this->input('phone'))
                    ->when($current, fn ($query) => $query->whereKeyNot($current->id))
                    ->first();

                if (! $existing) {
                    return;
                }

                if (! $existing->trashed()) {
                    $validator->errors()->add('phone', 'Ya existe un paciente con este teléfono: '.$existing->full_name.'.');
                    session()->flash('duplicate_patient_id', $existing->id);
                } elseif ($current) {
                    $validator->errors()->add('phone', 'Este teléfono pertenecía a un paciente eliminado. Usa otro número.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Escribe un teléfono válido (entre 6 y 15 dígitos).',
            'emergency_contact_phone.regex' => 'Escribe un teléfono de emergencia válido (entre 6 y 15 dígitos).',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ];
    }

    public function attributes(): array
    {
        return [
            'birth_date' => 'fecha de nacimiento',
            'gender' => 'género',
            'dni' => 'documento de identidad',
            'address' => 'dirección',
            'city' => 'ciudad',
            'postal_code' => 'código postal',
            'occupation' => 'ocupación',
            'emergency_contact_name' => 'contacto de emergencia',
            'emergency_contact_phone' => 'teléfono de emergencia',
            'preferred_modality' => 'modalidad preferida',
            'status' => 'estado',
            'therapy_type' => 'enfoque o tipo de terapia',
            'reason' => 'motivo de consulta',
            'notes' => 'notas',
        ];
    }
}
