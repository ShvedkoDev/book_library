<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\Cms\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download File')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $media = $this->record;

                    // Increment download count
                    $downloads = $media->getCustomProperty('download_count', 0);
                    $media->setCustomProperty('download_count', $downloads + 1);
                    $media->save();

                    return response()->download($media->getPath(), $media->file_name);
                }),

            Actions\Action::make('view_original')
                ->label('View Original')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => $this->record->getUrl())
                ->openUrlInNewTab()
                ->visible(fn () => str_starts_with($this->record->mime_type, 'image/')),

            Actions\Action::make('optimize')
                ->label('Optimize File')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->visible(fn () => str_starts_with($this->record->mime_type, 'image/'))
                ->requiresConfirmation()
                ->modalHeading('Optimize Media File')
                ->modalDescription('This will compress and optimize the image file. The original file will be replaced.')
                ->modalSubmitActionLabel('Optimize')
                ->action(function () {
                    $mediaService = app(MediaService::class);
                    $mediaService->optimizeMedia($this->record);

                    Notification::make()
                        ->title('Media optimized successfully')
                        ->success()
                        ->send();

                    $this->refreshFormData(['size']);
                }),

            Actions\Action::make('regenerate_conversions')
                ->label('Regenerate Thumbnails')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => str_starts_with($this->record->mime_type, 'image/'))
                ->requiresConfirmation()
                ->modalHeading('Regenerate Image Conversions')
                ->modalDescription('This will regenerate all thumbnails and image conversions for this media file.')
                ->modalSubmitActionLabel('Regenerate')
                ->action(function () {
                    $media = $this->record;

                    // Clear existing conversions
                    $media->clearMediaConversions();

                    // Regenerate conversions
                    if ($media->model) {
                        $media->model->registerMediaConversions($media);
                    }

                    Notification::make()
                        ->title('Image conversions regenerated successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('generate_alt_text')
                ->label('Generate Alt Text')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->visible(fn () => str_starts_with($this->record->mime_type, 'image/') &&
                    config('cms.media.auto_alt_text', false))
                ->action(function () {
                    $mediaService = app(MediaService::class);
                    $altText = $mediaService->generateAltText($this->record);

                    if ($altText) {
                        $this->form->fill([
                            'custom_properties.alt' => $altText
                        ]);

                        Notification::make()
                            ->title('Alt text generated successfully')
                            ->body("Generated: {$altText}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Could not generate alt text')
                            ->body('AI service may not be configured or available.')
                            ->warning()
                            ->send();
                    }
                }),

            Actions\ViewAction::make(),

            Actions\DeleteAction::make()
                ->before(function () {
                    $mediaService = app(MediaService::class);

                    if ($mediaService->isMediaInUse($this->record)) {
                        Notification::make()
                            ->title('Cannot delete media')
                            ->body('This media file is currently in use and cannot be deleted.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Media file updated successfully';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle custom properties separately
        if (isset($data['custom_properties'])) {
            $customProperties = array_merge(
                $this->record->custom_properties ?? [],
                array_filter($data['custom_properties'])
            );

            unset($data['custom_properties']);
            $data['custom_properties'] = $customProperties;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Update view count
        $viewCount = $this->record->getCustomProperty('view_count', 0);
        $this->record->setCustomProperty('view_count', $viewCount + 1);
        $this->record->save();
    }
}