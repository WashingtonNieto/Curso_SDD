<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VacationPeriod extends Model
{
    protected $fillable = ['start_date', 'end_date', 'note'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function scopeCovering(Builder $query, CarbonInterface $date): Builder
    {
        $day = $date->toDateString();

        return $query->whereDate('start_date', '<=', $day)->whereDate('end_date', '>=', $day);
    }

    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->whereDate('start_date', '<=', $end)->whereDate('end_date', '>=', $start);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('end_date', '>=', now()->toDateString())->orderBy('start_date');
    }
}
