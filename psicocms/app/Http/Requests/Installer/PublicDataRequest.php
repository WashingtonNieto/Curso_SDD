<?php

namespace App\Http\Requests\Installer;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

class PublicDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $services = collect($this->input('services', []))
            ->filter(fn ($service) => is_array($service) && trim((string) ($service['title'] ?? '')) !== '')
            ->values()
            ->all();

        $specialties = collect($this->input('specialties', []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();

        $this->merge([
            'booking_phone' => Phone::normalize($this->input('booking_phone')),
            'whatsapp' => Phone::normalize($this->input('whatsapp')),
            'services' => $services,
            'specialties' => $specialties,
        ]);
    }

    public function rules(): array
    {
        return [
            'public_name' => ['required', 'string', 'max:150'],
            'slogan' => ['nullable', 'string', 'max:200'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'booking_phone' => ['required', 'regex:'.Phone::PATTERN],
            'booking_email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'regex:'.Phone::PATTERN],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'about' => ['nullable', 'string', 'max:20000'],
            'specialties' => ['array', 'max:30'],
            'specialties.*' => ['string', 'max:100'],
            'services' => ['array', 'max:20'],
            'services.*.title' => ['required', 'string', 'max:150'],
            'services.*.description' => ['nullable', 'string', 'max:500'],
            'plans.online.price' => ['nullable', 'numeric', 'min:0', 'max:'.config('psicocms.currency.max_price')],
            'plans.online.description' => ['nullable', 'string', 'max:300'],
            'plans.presencial.price' => ['nullable', 'numeric', 'min:0', 'max:'.config('psicocms.currency.max_price')],
            'plans.presencial.description' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_phone.regex' => 'Escribe un teléfono para citas válido (entre 6 y 15 dígitos).',
            'whatsapp.regex' => 'Escribe un número de WhatsApp válido (entre 6 y 15 dígitos).',
        ];
    }

    public function attributes(): array
    {
        return [
            'services.*.title' => 'título del servicio',
            'services.*.description' => 'descripción del servicio',
            'specialties.*' => 'especialidad',
            'plans.online.price' => 'precio online',
            'plans.presencial.price' => 'precio presencial',
            'plans.online.description' => 'descripción del plan online',
            'plans.presencial.description' => 'descripción del plan presencial',
        ];
    }
}
