<?php

namespace App\Http\Requests\Installer;

use App\Services\ThemeManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::in(array_keys(app(ThemeManager::class)->all()))],
            'theme_mode' => ['required', Rule::in(array_keys(config('psicocms.theme_modes')))],
            'demo_content' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'theme.required' => 'Elige uno de los temas visuales.',
        ];
    }
}
