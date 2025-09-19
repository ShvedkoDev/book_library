<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\Cms\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload_multiple')
                ->label('Upload Multiple Files')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('primary')
                ->modalHeading('Upload Multiple Files')
                ->modalDescription('Select multiple files to upload to the media library.')
                ->modalSubmitActionLabel('Upload Files')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('files')
                        ->label('Select Files')
                        ->multiple()
                        ->disk('public')
                        ->directory('cms/media')
                        ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'image/svg+xml',
                            'application/pdf',
                            'video/mp4',
                            'video/webm',
                        ])
                        ->maxSize(51200) // 50MB
                        ->maxFiles(20)
                        ->required(),

                    \Filament\Forms\Components\Select::make('collection')
                        ->label('Collection')
                        ->options([
                            'page_featured' => 'Page Featured Images',
                            'page_gallery' => 'Page Gallery',
                            'content_blocks' => 'Content Block Media',
                            'documents' => 'Documents',
                            'videos' => 'Video Files',
                            'seo_images' => 'SEO Images',
                        ])
                        ->default('page_gallery')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $mediaService = app(MediaService::class);
                    $uploaded = 0;

                    foreach ($data['files'] as $file) {
                        try {
                            // Create a temporary model to attach media
                            $tempModel = new class implements \Spatie\MediaLibrary\HasMedia {
                                use \Spatie\MediaLibrary\InteractsWithMedia;
                                public $id = 'temp';
                            };

                            $media = $mediaService->uploadMedia($file, $data['collection'], $tempModel);
                            $uploaded++;
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Upload failed')
                                ->body("Failed to upload {$file->getClientOriginalName()}: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Upload completed')
                        ->body("Successfully uploaded {$uploaded} files")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Upload Single File'),

            Actions\Action::make('media_stats')
                ->label('Storage Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->modalHeading('Media Library Statistics')
                ->modalContent(function () {
                    $mediaService = app(MediaService::class);
                    $stats = $mediaService->getMediaUsageStats();

                    return view('filament.components.media-stats', compact('stats'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\Action::make('cleanup')
                ->label('Cleanup Unused')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clean Up Unused Media')
                ->modalDescription('This will permanently delete all unused media files. This action cannot be undone.')
                ->modalSubmitActionLabel('Delete Unused Files')
                ->action(function () {
                    $mediaService = app(MediaService::class);
                    $cleaned = $mediaService->cleanUnusedMedia();

                    \Filament\Notifications\Notification::make()
                        ->title('Cleanup completed')
                        ->body("Deleted " . count($cleaned) . " unused files")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Media')
                ->badge(Media::count()),

            'images' => Tab::make('Images')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'image/%'))
                ->badge(Media::where('mime_type', 'like', 'image/%')->count()),

            'documents' => Tab::make('Documents')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'application/%'))
                ->badge(Media::where('mime_type', 'like', 'application/%')->count()),

            'videos' => Tab::make('Videos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'video/%'))
                ->badge(Media::where('mime_type', 'like', 'video/%')->count()),

            'featured' => Tab::make('Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('custom_properties->featured', true))
                ->badge(Media::whereJsonContains('custom_properties->featured', true)->count()),

            'unused' => Tab::make('Unused')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('model_type'))
                ->badge(Media::whereNull('model_type')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MediaResource\Widgets\MediaStatsWidget::class,
        ];
    }
}