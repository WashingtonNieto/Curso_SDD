<?php

namespace App\Models;

use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, Sortable;

    public const MODALITIES = ['online' => 'Online', 'presencial' => 'Presencial', 'ambas' => 'Online y presencial'];

    protected $fillable = ['name', 'modality', 'price', 'duration_label', 'description', 'features', 'is_featured', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->features))));
    }

    public function scopeForModality(Builder $query, string $modality): Builder
    {
        return $query->whereIn('modality', [$modality, 'ambas']);
    }
}
