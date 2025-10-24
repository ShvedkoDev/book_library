<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);
        $stats = $analytics->getDashboardStats(30);

        return [
            Stat::make('Total Views (30 days)', $stats['total_views'])
                ->description('Book views in the last 30 days')
                ->descriptionIcon('heroicon-o-eye')
                ->color('success'),

            Stat::make('Total Downloads (30 days)', $stats['total_downloads'])
                ->description('File downloads in the last 30 days')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info'),

            Stat::make('Total Searches (30 days)', $stats['total_searches'])
                ->description('Search queries in the last 30 days')
                ->descriptionIcon('heroicon-o-magnifying-glass')
                ->color('warning'),

            Stat::make('Unique Books Viewed', $stats['unique_books_viewed'])
                ->description('Different books viewed in last 30 days')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),
        ];
    }
}
