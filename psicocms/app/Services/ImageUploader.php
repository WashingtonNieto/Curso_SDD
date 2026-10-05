<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploader
{
    public function store(UploadedFile $file, string $folder, ?string $previous = null): string
    {
        $path = $file->store('uploads/'.trim($folder, '/'), 'public');

        $this->delete($previous);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
