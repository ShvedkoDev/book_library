<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class UniqueUsersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Unique Users Over Time';
    protected static ?int $sort = 9;

    protected function getData(): array
    {
        $analytics = app(AnalyticsService::class);
        $dailyUsers = $analytics->getDailyUniqueUsers(30);

        $labels = [];
        $data = [];

        foreach ($dailyUsers as $date => $count) {
            $labels[] = \Carbon\Carbon::parse($date)->format('M j');
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unique Users',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
