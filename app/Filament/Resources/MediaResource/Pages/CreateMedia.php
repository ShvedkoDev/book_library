<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\Cms\MediaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $mediaService = app(MediaService::class);

        // Create a temporary model to attach media
        $tempModel = new class implements \Spatie\MediaLibrary\HasMedia {
            use \Spatie\MediaLibrary\InteractsWithMedia;
            public $id = 'temp';

            public function registerMediaCollections(): void
            {
                foreach (config('cms.media.collections', []) as $name => $config) {
                    $collection = $this->addMediaCollection($name);

                    if (isset($config['accepts_mime_types'])) {
                        $collection->acceptsMimeTypes($config['accepts_mime_types']);
                    }

                    if ($config['single_file'] ?? false) {
                        $collection->singleFile();
                    }

                    if (isset($config['disk'])) {
                        $collection->useDisk($config['disk']);
                    }

                    if (isset($config['path'])) {
                        $collection->usePath($config['path']);
                    }
                }
            }
        };

        if (isset($data['file'])) {
            $media = $mediaService->uploadMedia(
                $data['file'],
                $data['collection_name'],
                $tempModel
            );

            // Update media with additional data
            $media->update([
                'name' => $data['name'],
                'custom_properties' => array_merge(
                    $media->custom_properties ?? [],
                    array_filter([
                        'alt' => $data['custom_properties']['alt'] ?? null,
                        'title' => $data['custom_properties']['title'] ?? null,
                        'description' => $data['custom_properties']['description'] ?? null,
                        'caption' => $data['custom_properties']['caption'] ?? null,
                        'credit' => $data['custom_properties']['credit'] ?? null,
                        'keywords' => $data['custom_properties']['keywords'] ?? null,
                        'folder' => $data['custom_properties']['folder'] ?? 'general',
                        'featured' => $data['custom_properties']['featured'] ?? false,
                        'private' => $data['custom_properties']['private'] ?? false,
                        'expires_at' => $data['custom_properties']['expires_at'] ?? null,
                        'download_count' => 0,
                        'view_count' => 0,
                    ])
                )
            ]);

            return $media;
        }

        // Fallback to creating empty media record
        return Media::create($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Media file uploaded successfully';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['disk'] = $data['disk'] ?? 'public';

        return $data;
    }
}