<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalAttachment extends Model
{
    protected $fillable = ['clinical_entry_id', 'path', 'original_name', 'mime', 'size'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function entry()
    {
        return $this->belongsTo(ClinicalEntry::class, 'clinical_entry_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }
}
