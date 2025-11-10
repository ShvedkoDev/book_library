<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);

        return [
            Stat::make('Views Today', $analytics->getViewsToday())
                ->description('Book views in the last 24 hours')
                ->descriptionIcon('heroicon-o-eye')
                ->color('success'),

            Stat::make('Downloads Today', $analytics->getDownloadsToday())
                ->description('File downloads in the last 24 hours')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info'),

            Stat::make('Total Views (30 days)', $analytics->getViews(30))
                ->description('Book views in the last 30 days')
                ->descriptionIcon('heroicon-o-eye')
                ->color('success'),

            Stat::make('Total Downloads (30 days)', $analytics->getDownloads(30))
                ->description('File downloads in the last 30 days')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info'),

            Stat::make('Total Searches (30 days)', $analytics->getSearches(30))
                ->description('Search queries in the last 30 days')
                ->descriptionIcon('heroicon-o-magnifying-glass')
                ->color('warning'),

            Stat::make('Unique Book Views (30 days)', $analytics->getUniqueBooksViewed(30))
                ->description('Different books viewed in last 30 days')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),
        ];
    }
}
