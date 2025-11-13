<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookReview;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $reviewedBooks = Book::whereHas('reviews')->count();
        $totalReviews = BookReview::count();
        $pendingReviews = BookReview::where('is_approved', false)->count();
        $approvedReviews = BookReview::where('is_approved', true)->count();

        return [
            Stat::make('Reviewed books', $reviewedBooks)
                ->description('Books with at least one review')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->extraAttributes(['style' => 'background-color: #a299b8; color: white;']),

            Stat::make('Total reviews', $totalReviews)
                ->description($approvedReviews . ' approved, ' . $pendingReviews . ' pending')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->extraAttributes(['style' => 'background-color: #a299b8; color: white;']),

            Stat::make('Pending reviews', $pendingReviews)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingReviews > 0 ? 'warning' : 'success')
                ->extraAttributes(['style' => 'background-color: #a299b8; color: white;']),
        ];
    }
}
