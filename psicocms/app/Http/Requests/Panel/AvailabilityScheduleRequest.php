<?php

namespace App\Http\Requests\Panel;

use App\Http\Requests\Concerns\ValidatesSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AvailabilityScheduleRequest extends FormRequest
{
    use ValidatesSchedule;

    public function authorize(): bool
    {
        return true;
    }

    public function modality(): string
    {
        return (string) $this->route('modality');
    }

    public function rules(): array
    {
        return $this->scheduleRules($this->modality());
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateScheduleFits($validator, $this->modality())];
    }

    public function attributes(): array
    {
        return $this->scheduleAttributes($this->modality());
    }
}
