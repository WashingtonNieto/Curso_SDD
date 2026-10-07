<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    /**
     * @param  class-string<Model>  $model
     */
    public static function unique(string $model, ?string $value, ?int $ignoreId = null, string $fallback = 'entrada'): string
    {
        $base = trim(Str::limit(Str::slug((string) $value), 180, ''), '-') ?: $fallback;
        $slug = $base;
        $suffix = 2;

        while ($model::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
