<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class EditorImageController extends Controller
{
    public function __invoke(Request $request, ImageUploader $uploader): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ], [
            'images.required' => 'No se ha recibido ninguna imagen.',
            'images.max' => 'Puedes subir como máximo 10 imágenes a la vez.',
            'images.*.image' => 'El archivo debe ser una imagen.',
            'images.*.mimes' => 'La imagen debe ser JPG, PNG, WEBP o GIF.',
            'images.*.max' => 'Cada imagen puede pesar como máximo 4 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => ['messages' => array_values(array_unique($validator->errors()->all())), 'files' => []],
            ]);
        }

        $files = collect($request->file('images'))
            ->map(fn (UploadedFile $file) => '/storage/'.$uploader->store($file, 'editor'))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'baseurl' => '',
                'files' => $files,
                'isImages' => $files->map(fn () => true),
                'messages' => [],
            ],
        ]);
    }
}
