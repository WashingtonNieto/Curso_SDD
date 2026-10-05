<?php

namespace App\Http\Requests\Panel;

use App\Models\Appointment;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::normalize($this->input('phone')),
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'custom_time' => $this->boolean('custom_time'),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['required', 'regex:'.Phone::PATTERN],
            'email' => ['nullable', 'email', 'max:255'],
            'modality' => ['required', Rule::in(array_keys(Appointment::MODALITIES))],
            'date' => ['required', 'date_format:Y-m-d'],
            'custom_time' => ['boolean'],
            'slot_time' => ['exclude_if:custom_time,true', 'required', 'date_format:H:i'],
            'custom_time_value' => ['exclude_unless:custom_time,true', 'required', 'date_format:H:i'],
            'status' => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'source' => ['required', Rule::in(array_keys(Appointment::SOURCES))],
            'price' => ['nullable', 'numeric', 'min:0', 'max:'.config('psicocms.currency.max_price')],
            'reason' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function appointmentData(): array
    {
        $data = $this->validated();
        $data['time'] = $data['custom_time'] ? $data['custom_time_value'] : $data['slot_time'];

        return $data;
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Escribe un teléfono válido (entre 6 y 15 dígitos).',
            'slot_time.required' => 'Elige uno de los huecos libres o marca “Hora personalizada”.',
            'custom_time_value.required' => 'Indica la hora de la cita.',
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'fecha',
            'slot_time' => 'hora',
            'custom_time_value' => 'hora personalizada',
            'source' => 'origen',
            'internal_notes' => 'notas internas',
        ];
    }
}
