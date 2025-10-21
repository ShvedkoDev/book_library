<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Language;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BooksStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Books', Book::count())
                ->description('Books in library')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),
                
            Stat::make('Active Books', Book::where('is_active', true)->count())
                ->description('Currently available')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
                
            Stat::make('Featured Books', Book::where('is_featured', true)->count())
                ->description('Highlighted content')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),
                
            Stat::make('Languages', Language::where('is_active', true)->count())
                ->description('Available languages')
                ->descriptionIcon('heroicon-o-language')
                ->color('info'),
        ];
    }
}
