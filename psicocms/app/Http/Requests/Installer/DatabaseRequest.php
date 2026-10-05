<?php

namespace App\Http\Requests\Installer;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'db_host' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-_:]+$/'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_username' => ['required', 'string', 'max:100'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'db_database.regex' => 'El nombre de la base de datos solo puede contener letras sin tildes, números y guiones bajos (_).',
            'db_host.regex' => 'El nombre del servidor no es válido.',
        ];
    }
}
