<?php

namespace App\Models;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['activo' => 'Activo', 'pausado' => 'En pausa', 'alta' => 'Alta'];

    public const GENDERS = ['mujer' => 'Mujer', 'hombre' => 'Hombre', 'no_binario' => 'No binario', 'otro' => 'Otro'];

    protected $fillable = [
        'phone',
        'first_name',
        'last_name',
        'email',
        'birth_date',
        'gender',
        'dni',
        'address',
        'city',
        'postal_code',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferred_modality',
        'status',
        'therapy_type',
        'reason',
        'notes',
        'source',
        'privacy_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'privacy_signed_at' => 'datetime',
        ];
    }

    protected function phone(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => Phone::normalize($value));
    }

    protected function emergencyContactPhone(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => Phone::normalize($value));
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn () => trim($this->first_name.' '.$this->last_name));
    }

    protected function initials(): Attribute
    {
        return Attribute::make(get: fn () => mb_strtoupper(
            mb_substr($this->first_name ?? '', 0, 1).mb_substr($this->last_name ?? '', 0, 1)
        ));
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function clinicalEntries()
    {
        return $this->hasMany(ClinicalEntry::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'activo');
    }
}
