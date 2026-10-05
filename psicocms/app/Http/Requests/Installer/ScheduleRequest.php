<?php

namespace App\Http\Requests\Installer;

use App\Http\Requests\Concerns\ValidatesSchedule;
use App\Models\AvailabilitySetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScheduleRequest extends FormRequest
{
    use ValidatesSchedule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $rules += $this->scheduleRules($modality) + [
                "$modality.weekdays" => ['array'],
                "$modality.weekdays.*" => ['integer', 'between:1,7'],
            ];
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach (AvailabilitySetting::MODALITIES as $modality) {
                    $this->validateScheduleFits($validator, $modality);
                }
            },
        ];
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $attributes += $this->scheduleAttributes($modality);
        }

        return $attributes;
    }
}
