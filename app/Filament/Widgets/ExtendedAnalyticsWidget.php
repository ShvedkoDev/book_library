<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExtendedAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);

        return [
            Stat::make('Total views (1 year)', $analytics->getViews(365))
                ->description('Book views in the last 365 days')
                ->descriptionIcon('heroicon-o-eye')
                ->color('success')
                ->extraAttributes([
                    'style' => 'background-color: #b8caa5;',
                    'class' => 'widget-light-bg'
                ]),

            Stat::make('Total downloads (1 year)', $analytics->getDownloads(365))
                ->description('File downloads in the last 365 days')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->extraAttributes([
                    'style' => 'background-color: #b8caa5;',
                    'class' => 'widget-light-bg'
                ]),

            Stat::make('Total searches (1 year)', $analytics->getSearches(365))
                ->description('Search queries in the last 365 days')
                ->descriptionIcon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->extraAttributes([
                    'style' => 'background-color: #b8caa5;',
                    'class' => 'widget-light-bg'
                ]),

            Stat::make('Unique book views (1 year)', $analytics->getUniqueBooksViewed(365))
                ->description('Different books viewed in last 365 days')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'background-color: #b8caa5;',
                    'class' => 'widget-light-bg'
                ]),
        ];
    }
}
