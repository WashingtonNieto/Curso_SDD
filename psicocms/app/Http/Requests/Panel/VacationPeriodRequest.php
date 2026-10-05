<?php

namespace App\Http\Requests\Panel;

use App\Models\VacationPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VacationPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $overlap = VacationPeriod::overlapping($this->input('start_date'), $this->input('end_date'))->orderBy('start_date')->first();

                if ($overlap) {
                    $validator->errors()->add('start_date', sprintf(
                        'Estas fechas se solapan con otro periodo de vacaciones (del %s al %s).',
                        $overlap->start_date->format('d/m/Y'),
                        $overlap->end_date->format('d/m/Y')
                    ));
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ];
    }
}
