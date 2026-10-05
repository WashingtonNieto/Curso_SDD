<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class WeeklySlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slots' => ['present', 'array', 'max:700'],
            'slots.*.weekday' => ['required', 'integer', 'between:1,7'],
            'slots.*.time' => ['required', 'date_format:H:i'],
        ];
    }
}
