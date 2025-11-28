<?php

namespace App\Filament\Resources\DataQualityIssueResource\Pages;

use App\Filament\Resources\DataQualityIssueResource;
use App\Services\DataQualityService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListDataQualityIssues extends ListRecords
{
    protected static string $resource = DataQualityIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('run_quality_checks')
                ->label('Run quality checks')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Run data quality checks')
                ->modalDescription('This will check all books for data quality issues. This may take a few moments.')
                ->modalSubmitActionLabel('Run checks')
                ->action(function (DataQualityService $qualityService): void {
                    try {
                        // Run quality checks on all books
                        $report = $qualityService->runQualityChecks(
                            null, // Check all books
                            null, // No specific import
                            false // Don't clear existing
                        );

                        Notification::make()
                            ->title('Quality Checks Completed')
                            ->body("Checked {$report['total_books_checked']} books. Found {$report['total_issues_found']} issues ({$report['critical_issues']} critical, {$report['warnings']} warnings, {$report['info_issues']} info).")
                            ->success()
                            ->duration(10000)
                            ->send();

                        // Refresh the table
                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Quality Check Failed')
                            ->body('An error occurred: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('view_summary')
                ->label('View summary')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->action(function (DataQualityService $qualityService): void {
                    $summary = $qualityService->getUnresolvedIssuesSummary();

                    $body = "Total Unresolved: {$summary['total']}\n";
                    $body .= "Critical: {$summary['critical']}, ";
                    $body .= "Warnings: {$summary['warnings']}, ";
                    $body .= "Info: {$summary['info']}\n\n";

                    if (!empty($summary['by_type'])) {
                        $body .= "Top Issues:\n";
                        $topIssues = array_slice($summary['by_type'], 0, 5, true);
                        foreach ($topIssues as $type => $count) {
                            $body .= "- " . str_replace('_', ' ', ucwords($type, '_')) . ": {$count}\n";
                        }
                    }

                    Notification::make()
                        ->title('Data Quality Summary')
                        ->body($body)
                        ->info()
                        ->duration(15000)
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Could add a stats widget here showing totals by severity
        ];
    }
}
