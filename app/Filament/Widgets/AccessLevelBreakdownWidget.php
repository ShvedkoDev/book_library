<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccessLevelBreakdownWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $fullAccessCount = Book::where('access_level', 'full')->count();
        $limitedAccessCount = Book::where('access_level', 'limited')->count();
        $unavailableCount = Book::where('access_level', 'unavailable')->count();
        $totalBooks = Book::count();

        return [
            Stat::make('Full access books', $fullAccessCount)
                ->description(number_format(($fullAccessCount / max($totalBooks, 1)) * 100, 1) . '% of total books')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes([
                    'style' => 'background-color: #80a4aa;',
                    'class' => 'widget-dark-bg'
                ]),

            Stat::make('Limited access books', $limitedAccessCount)
                ->description(number_format(($limitedAccessCount / max($totalBooks, 1)) * 100, 1) . '% of total books')
                ->descriptionIcon('heroicon-o-lock-closed')
                ->color('warning')
                ->extraAttributes([
                    'style' => 'background-color: #80a4aa;',
                    'class' => 'widget-dark-bg'
                ]),

            Stat::make('Unavailable books', $unavailableCount)
                ->description(number_format(($unavailableCount / max($totalBooks, 1)) * 100, 1) . '% of total books')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'style' => 'background-color: #80a4aa;',
                    'class' => 'widget-dark-bg'
                ]),
        ];
    }
}
