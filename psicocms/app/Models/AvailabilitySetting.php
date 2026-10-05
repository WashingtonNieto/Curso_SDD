<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilitySetting extends Model
{
    public const MODALITIES = ['online', 'presencial'];

    protected $fillable = [
        'modality',
        'session_duration',
        'break_enabled',
        'break_minutes',
        'day_start',
        'day_end',
        'needs_review',
    ];

    protected function casts(): array
    {
        return [
            'session_duration' => 'integer',
            'break_enabled' => 'boolean',
            'break_minutes' => 'integer',
            'needs_review' => 'boolean',
        ];
    }

    public function effectiveBreak(): int
    {
        return $this->break_enabled ? $this->break_minutes : 0;
    }

    public function step(): int
    {
        return $this->session_duration + $this->effectiveBreak();
    }

    public function slots()
    {
        return $this->hasMany(AvailabilitySlot::class, 'modality', 'modality');
    }

    public static function forModality(string $modality): self
    {
        return static::firstOrCreate(['modality' => $modality]);
    }
}
