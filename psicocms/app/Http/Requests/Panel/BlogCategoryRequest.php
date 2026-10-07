<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('blog_categories', 'name')->ignore($this->route('category'))],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'slug' => 'dirección (slug)',
            'description' => 'descripción',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya tienes una categoría con ese nombre.',
        ];
    }

    public function categoryData(): array
    {
        $description = trim(strip_tags((string) $this->input('description')));

        return [
            'name' => $this->input('name'),
            'description' => $description === '' ? null : $description,
        ];
    }
}
