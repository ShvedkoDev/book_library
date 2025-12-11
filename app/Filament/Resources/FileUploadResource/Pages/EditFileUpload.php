<?php

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditFileUpload extends EditRecord
{
    protected static string $resource = FileUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If file was replaced, update metadata
        if (isset($data['file_path']) && $data['file_path'] !== $this->record->file_path) {
            $filePath = $data['file_path'];
            $fullPath = storage_path('app/' . $filePath);

            if (file_exists($fullPath)) {
                $fileName = basename($filePath);
                $data['file_name'] = $fileName;
                $data['original_name'] = $fileName;
                $data['mime_type'] = mime_content_type($fullPath) ?: 'application/octet-stream';
                $data['file_size'] = filesize($fullPath);
            }
        }

        return $data;
    }
}
