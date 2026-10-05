<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['patient_id', 'appointment_id', 'session_date', 'title', 'content'];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function attachments()
    {
        return $this->hasMany(ClinicalAttachment::class);
    }
}
