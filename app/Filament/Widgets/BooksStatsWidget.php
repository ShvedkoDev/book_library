<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Language;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BooksStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total books', Book::count())
                ->description('Books in library')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary')
                ->extraAttributes(['style' => 'background-color: #80a4aa; color: white;']),

            Stat::make('Active books', Book::where('is_active', true)->count())
                ->description('Currently available')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes(['style' => 'background-color: #b4d8d4; color: #333;']),

            Stat::make('Featured books', Book::where('is_featured', true)->count())
                ->description('Highlighted content')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning')
                ->extraAttributes(['style' => 'background-color: #b4d8d4; color: #333;']),

            Stat::make('Languages', Language::where('is_active', true)->count())
                ->description('Available languages')
                ->descriptionIcon('heroicon-o-language')
                ->color('info')
                ->extraAttributes(['style' => 'background-color: #dd785b; color: white;']),
        ];
    }
}
