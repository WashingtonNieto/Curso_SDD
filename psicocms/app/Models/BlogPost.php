<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    public const STATUSES = ['borrador' => 'Borrador', 'publicado' => 'Publicado'];

    protected $fillable = ['blog_category_id', 'title', 'slug', 'excerpt', 'content', 'image_path', 'status', 'published_at', 'meta_description'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'publicado')->where('published_at', '<=', now());
    }
}
