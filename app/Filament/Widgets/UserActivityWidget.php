<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\BookDownload;
use App\Models\BookReview;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserActivityWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $adminUsers = User::where('role', 'admin')->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Verified Users', $verifiedUsers)
                ->description('Email verified')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Admin Users', $adminUsers)
                ->description('Administrator accounts')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('warning'),
        ];
    }
}
