<?php

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateFileUpload extends CreateRecord
{
    protected static string $resource = FileUploadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract file metadata from uploaded file
        if (isset($data['file_path'])) {
            $filePath = $data['file_path'];

            // Get the full path
            $fullPath = storage_path('app/' . $filePath);

            if (file_exists($fullPath)) {
                // Extract filename from path
                $fileName = basename($filePath);

                // Get file info
                $data['file_name'] = $fileName;
                $data['original_name'] = $fileName;
                $data['mime_type'] = mime_content_type($fullPath) ?: 'application/octet-stream';
                $data['file_size'] = filesize($fullPath);
            }
        }

        return $data;
    }
}
