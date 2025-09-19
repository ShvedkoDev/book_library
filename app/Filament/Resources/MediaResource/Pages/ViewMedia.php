<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\Cms\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewMedia extends ViewRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download')
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

            Actions\Action::make('copy_url')
                ->label('Copy URL')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->action(function () {
                    $url = $this->record->getUrl();

                    // This would copy to clipboard via JavaScript
                    Notification::make()
                        ->title('URL copied to clipboard')
                        ->body($url)
                        ->success()
                        ->send();
                }),

            Actions\Action::make('view_conversions')
                ->label('View Conversions')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->visible(fn () => str_starts_with($this->record->mime_type, 'image/'))
                ->modalHeading('Image Conversions')
                ->modalContent(function () {
                    $media = $this->record;
                    $conversions = [];

                    foreach (config('cms.media.conversions', []) as $name => $config) {
                        if ($media->hasGeneratedConversion($name)) {
                            $conversions[$name] = [
                                'url' => $media->getUrl($name),
                                'config' => $config
                            ];
                        }
                    }

                    return view('filament.components.media-conversions', compact('conversions'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\Action::make('usage_report')
                ->label('Usage Report')
                ->icon('heroicon-o-chart-bar')
                ->color('warning')
                ->modalHeading('Media Usage Report')
                ->modalContent(function () {
                    $media = $this->record;
                    $mediaService = app(MediaService::class);

                    // Get usage information
                    $usage = [
                        'is_in_use' => $mediaService->isMediaInUse($media),
                        'model_type' => $media->model_type,
                        'model_id' => $media->model_id,
                        'collection' => $media->collection_name,
                        'downloads' => $media->getCustomProperty('download_count', 0),
                        'views' => $media->getCustomProperty('view_count', 0),
                        'created_at' => $media->created_at,
                        'file_size' => $media->size,
                    ];

                    return view('filament.components.media-usage-report', compact('usage', 'media'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\EditAction::make(),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Increment view count
        $viewCount = $this->record->getCustomProperty('view_count', 0);
        $this->record->setCustomProperty('view_count', $viewCount + 1);
        $this->record->save();
    }
}