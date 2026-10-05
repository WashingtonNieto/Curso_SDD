<?php

namespace App\Http\Requests\Concerns;

use App\Services\AvailabilityService;
use Illuminate\Validation\Validator;

trait ValidatesSchedule
{
    protected function scheduleRules(string $modality): array
    {
        return [
            "$modality.session_duration" => ['required', 'integer', 'between:15,240'],
            "$modality.break_enabled" => ['boolean'],
            "$modality.break_minutes" => ['required_if_accepted:'.$modality.'.break_enabled', 'nullable', 'integer', 'between:5,'.AvailabilityService::MAX_BREAK_MINUTES],
            "$modality.day_start" => ['required', 'date_format:H:i'],
            "$modality.day_end" => ['required', 'date_format:H:i', 'after:'.$modality.'.day_start'],
        ];
    }

    protected function scheduleAttributes(string $modality): array
    {
        return [
            "$modality.session_duration" => "duración de la sesión ($modality)",
            "$modality.break_minutes" => "minutos de descanso ($modality)",
            "$modality.break_enabled" => "descanso entre sesiones ($modality)",
            "$modality.day_start" => "hora de entrada ($modality)",
            "$modality.day_end" => "hora de salida ($modality)",
            "$modality.weekdays" => "días de consulta ($modality)",
        ];
    }

    protected function validateScheduleFits(Validator $validator, string $modality): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $data = $this->input($modality);
        $break = ! empty($data['break_enabled']) ? (int) $data['break_minutes'] : 0;

        if (AvailabilityService::times((int) $data['session_duration'], $break, $data['day_start'], $data['day_end']) === []) {
            $validator->errors()->add(
                "$modality.day_end",
                'Con este horario no cabe ninguna sesión. Amplía la hora de salida o reduce la duración.'
            );
        }
    }
}
