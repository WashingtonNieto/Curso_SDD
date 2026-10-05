<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    public $timestamps = false;

    protected $fillable = ['modality', 'weekday', 'start_time'];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
        ];
    }

    public function scopeModality(Builder $query, string $modality): Builder
    {
        return $query->where('modality', $modality);
    }
}
