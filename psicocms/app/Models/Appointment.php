<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    public const MODALITIES = ['online' => 'Online', 'presencial' => 'Presencial'];

    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'no_asistio' => 'No asistió',
    ];

    public const SOURCES = [
        'web' => 'Web',
        'telefono' => 'Teléfono',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'presencial' => 'En persona',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'patient_id',
        'modality',
        'starts_at',
        'ends_at',
        'break_minutes',
        'status',
        'source',
        'reason',
        'internal_notes',
        'price',
        'public_token',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'break_minutes' => 'integer',
            'price' => 'decimal:2',
            'seen_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            $appointment->public_token ??= (string) Str::uuid();
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function clinicalEntries()
    {
        return $this->hasMany(ClinicalEntry::class);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelada');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('starts_at', $date);
    }
}
