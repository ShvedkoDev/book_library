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
            Stat::make('Rated Books', $booksWithRatings)
                ->description('Books with ratings')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary'),

            Stat::make('Total Ratings', $totalRatings)
                ->description('User ratings submitted')
                ->descriptionIcon('heroicon-o-heart')
                ->color('info'),

            Stat::make('Average Rating', number_format($averageRating, 1) . '/5')
                ->description('Overall book quality')
                ->descriptionIcon('heroicon-o-star')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),

            Stat::make('5-Star Books', $topRatedBooks)
                ->description('Excellent rated books')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
        ];
    }
}
