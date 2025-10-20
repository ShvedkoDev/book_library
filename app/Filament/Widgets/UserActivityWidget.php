<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\BookDownload;
use App\Models\BookReview;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserActivityWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('email_verified_at', '!=', null)->count();
        $downloadsToday = BookDownload::whereDate('downloaded_at', today())->count();
        $pendingReviews = BookReview::where('is_approved', false)->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
                
            Stat::make('Verified Users', $activeUsers)
                ->description('Email verified')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
                
            Stat::make('Downloads Today', $downloadsToday)
                ->description('Books downloaded today')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info'),
                
            Stat::make('Pending Reviews', $pendingReviews)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingReviews > 0 ? 'warning' : 'success'),
        ];
    }
}
