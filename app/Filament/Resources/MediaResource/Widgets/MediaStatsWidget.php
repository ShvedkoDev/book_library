<?php

namespace App\Filament\Resources\MediaResource\Widgets;

use App\Services\Cms\MediaService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $mediaService = app(MediaService::class);
        $stats = $mediaService->getMediaUsageStats();

        return [
            Stat::make('Total Files', $stats['total_files'])
                ->description('Files in media library')
                ->descriptionIcon('heroicon-m-document')
                ->color('primary'),

            Stat::make('Storage Used', $this->formatBytes($stats['total_size']))
                ->description('Total storage usage')
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),

            Stat::make('Images', $stats['images'])
                ->description('Image files')
                ->descriptionIcon('heroicon-m-photo')
                ->color('info'),

            Stat::make('Documents', $stats['documents'])
                ->description('PDF and document files')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Videos', $stats['videos'])
                ->description('Video files')
                ->descriptionIcon('heroicon-m-play')
                ->color('danger'),

            Stat::make('Unused Files', Media::whereNull('model_type')->count())
                ->description('Files not attached to content')
                ->descriptionIcon('heroicon-m-trash')
                ->color('gray'),
        ];
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}