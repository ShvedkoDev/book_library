<?php

namespace App\Filament\Widgets;

use App\Models\BookDownload;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DownloadsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Downloads Over Time';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $downloads = BookDownload::selectRaw('DATE(downloaded_at) as date, COUNT(*) as count')
            ->where('downloaded_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            $data[] = $downloads[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Downloads',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
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
