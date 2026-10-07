<?php

namespace App\Http\Requests\Panel;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class BlogPostRequest extends FormRequest
{
    public const META_MAX = 160;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200'],
            'blog_category_id' => ['nullable', 'integer', Rule::exists('blog_categories', 'id')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:500000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(BlogPost::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'meta_description' => ['nullable', 'string', 'max:'.self::META_MAX],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'slug' => 'dirección (slug)',
            'blog_category_id' => 'categoría',
            'excerpt' => 'extracto',
            'content' => 'contenido',
            'image' => 'imagen destacada',
            'status' => 'estado',
            'published_at' => 'fecha de publicación',
            'meta_description' => 'meta descripción',
        ];
    }

    public function messages(): array
    {
        return [
            'image.max' => 'La imagen destacada puede pesar como máximo 4 MB.',
            'image.mimes' => 'La imagen destacada debe ser JPG, PNG o WEBP.',
        ];
    }

    public function postData(): array
    {
        $publishedAt = $this->filled('published_at') ? Carbon::parse($this->input('published_at'))->startOfMinute() : null;

        if ($this->input('status') === 'publicado' && $publishedAt === null) {
            $publishedAt = now()->startOfMinute();
        }

        return [
            'title' => trim($this->input('title')),
            'blog_category_id' => $this->input('blog_category_id') ?: null,
            'excerpt' => $this->plainText('excerpt'),
            'content' => $this->input('content'),
            'status' => $this->input('status'),
            'published_at' => $publishedAt,
            'meta_description' => $this->plainText('meta_description'),
        ];
    }

    private function plainText(string $key): ?string
    {
        $value = trim(strip_tags((string) $this->input($key)));

        return $value === '' ? null : $value;
    }
}
