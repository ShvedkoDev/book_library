<?php

namespace App\Filament\Widgets;

use App\Models\BookRating;
use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RatingAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $averageRating = BookRating::avg('rating') ?? 0;
        $totalRatings = BookRating::count();
        $booksWithRatings = Book::whereHas('ratings')->count();
        $topRatedBooks = BookRating::selectRaw('COUNT(*) as count')
            ->where('rating', 5)
            ->value('count') ?? 0;

        return [
            Stat::make('Rated books', $booksWithRatings)
                ->description('Books with ratings')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary')
                ->extraAttributes(['style' => 'background-color: #d2cbe3; color: #1a1a1a;']),

            Stat::make('Total ratings', $totalRatings)
                ->description('User ratings submitted')
                ->descriptionIcon('heroicon-o-heart')
                ->color('info')
                ->extraAttributes(['style' => 'background-color: #d2cbe3; color: #1a1a1a;']),

            Stat::make('Average rating', number_format($averageRating, 1) . '/5')
                ->description('Overall book quality')
                ->descriptionIcon('heroicon-o-star')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger'))
                ->extraAttributes(['style' => 'background-color: #d2cbe3; color: #1a1a1a;']),

            Stat::make('5-star books', $topRatedBooks)
                ->description('Excellent rated books')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success')
                ->extraAttributes(['style' => 'background-color: #d2cbe3; color: #1a1a1a;']),
        ];
    }
}
