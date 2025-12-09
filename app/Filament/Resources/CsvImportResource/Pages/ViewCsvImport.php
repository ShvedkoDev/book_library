<?php

namespace App\Filament\Resources\CsvImportResource\Pages;

use App\Filament\Resources\CsvImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCsvImport extends ViewRecord
{
    protected static string $resource = CsvImportResource::class;

    // Disable form rendering - we use infolist only
    protected static bool $canEdit = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Import Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('filename')
                            ->label('File name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Imported by'),
                        Infolists\Components\TextEntry::make('mode')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'create_only' => 'Create Only',
                                'update_only' => 'Update Only',
                                'upsert' => 'Upsert',
                                'create_duplicates' => 'Create Duplicates',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'failed' => 'danger',
                                'processing' => 'warning',
                                'pending' => 'gray',
                                'cancelled' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    ])->columns(2),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_rows')
                                    ->label('Total rows'),
                                Infolists\Components\TextEntry::make('processed_rows')
                                    ->label('Processed'),
                                Infolists\Components\TextEntry::make('successful_rows')
                                    ->label('Successful')
                                    ->color('success'),
                            ]),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_count')
                                    ->label('Created')
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('updated_count')
                                    ->label('Updated')
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('failed_rows')
                                    ->label('Failed')
                                    ->color('danger'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('success_rate')
                                    ->label('Success rate')
                                    ->formatStateUsing(fn ($state) => number_format((float)$state, 2) . '%')
                                    ->color(fn ($state) => (float)$state >= 90 ? 'success' : ((float)$state >= 70 ? 'warning' : 'danger')),
                            ]),
                    ]),

                Infolists\Components\Section::make('Timing')
                    ->schema([
                        Infolists\Components\TextEntry::make('started_at')
                            ->dateTime()
                            ->label('Started at'),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->dateTime()
                            ->label('Completed at'),
                        Infolists\Components\TextEntry::make('duration_seconds')
                            ->label('Duration')
                            ->formatStateUsing(fn ($state) => $state ? round($state, 1) . ' seconds' : 'N/A'),
                    ])->columns(3),

                Infolists\Components\Section::make('Skipped Books Report')
                    ->description('Books that were not created or updated during import')
                    ->schema([
                        Infolists\Components\TextEntry::make('skipped_books')
                            ->label('')
                            ->default(function ($record) {
                                $state = $record->skipped_log;
                                
                                if (empty($state) || !is_array($state)) {
                                    return 'No books were skipped during this import.';
                                }

                                $totalBooks = count($state);
                                $html = '<div class="space-y-4">';

                                // Summary
                                $html .= '<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">';
                                $html .= '<div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Total Skipped: <span class="text-xl font-bold">' . $totalBooks . '</span></div>';
                                $html .= '</div>';

                                // Table
                                $html .= '<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">';
                                $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                                $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
                                $html .= '<tr>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Row #</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IDs</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>';
                                $html .= '</tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($state as $book) {
                                    $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Row ' . ($book['row'] ?? 'Unknown') . '</span></td>';
                                    $html .= '<td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">' . e($book['title'] ?? 'Unknown') . '</td>';
                                    $html .= '<td class="px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">';
                                    if (!empty($book['internal_id'])) $html .= 'Internal: ' . e($book['internal_id']) . '<br>';
                                    if (!empty($book['palm_code'])) $html .= 'PALM: ' . e($book['palm_code']);
                                    $html .= '</td>';
                                    $html .= '<td class="px-6 py-4 text-sm"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">' . e($book['reason'] ?? 'N/A') . '</span></td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody>';
                                $html .= '</table>';
                                $html .= '</div>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->html(),
                    ])
                    ->collapsed()
                    ->visible(function ($record) {
                        $log = $record->skipped_log;
                        return !empty($log) && is_array($log) && count($log) > 0;
                    }),

                Infolists\Components\Section::make('Updated Books Report')
                    ->description('Books that were updated during import')
                    ->schema([
                        Infolists\Components\TextEntry::make('updated_books')
                            ->label('')
                            ->default(function ($record) {
                                $state = $record->updated_log;
                                
                                if (empty($state) || !is_array($state)) {
                                    return 'No books were updated during this import.';
                                }

                                $totalBooks = count($state);
                                $html = '<div class="space-y-4">';

                                // Summary
                                $html .= '<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">';
                                $html .= '<div class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Updated: <span class="text-xl font-bold">' . $totalBooks . '</span></div>';
                                $html .= '</div>';

                                // Table
                                $html .= '<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">';
                                $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                                $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
                                $html .= '<tr>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Row #</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IDs</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Changes</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>';
                                $html .= '</tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($state as $book) {
                                    $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Row ' . ($book['row'] ?? 'Unknown') . '</span></td>';
                                    $html .= '<td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">' . e($book['title'] ?? 'Unknown') . '</td>';
                                    $html .= '<td class="px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">';
                                    if (!empty($book['internal_id'])) $html .= 'Internal: ' . e($book['internal_id']) . '<br>';
                                    if (!empty($book['palm_code'])) $html .= 'PALM: ' . e($book['palm_code']);
                                    $html .= '</td>';
                                    
                                    // Changes column
                                    $html .= '<td class="px-6 py-4 text-xs">';
                                    if (!empty($book['changes']) && is_array($book['changes'])) {
                                        $html .= '<div class="space-y-1">';
                                        foreach ($book['changes'] as $field => $change) {
                                            $fieldLabel = ucwords(str_replace('_', ' ', $field));
                                            $html .= '<div class="text-gray-600 dark:text-gray-400">';
                                            $html .= '<span class="font-semibold">' . e($fieldLabel) . ':</span><br>';
                                            $html .= '<span class="text-red-600 dark:text-red-400 line-through">' . e($change['old'] ?? 'empty') . '</span> ';
                                            $html .= '<span class="text-gray-400">→</span> ';
                                            $html .= '<span class="text-green-600 dark:text-green-400">' . e($change['new'] ?? 'empty') . '</span>';
                                            $html .= '</div>';
                                        }
                                        $html .= '</div>';
                                    } else {
                                        $html .= '<span class="text-gray-400">No changes recorded</span>';
                                    }
                                    $html .= '</td>';
                                    
                                    $html .= '<td class="px-6 py-4 text-sm">';
                                    if (!empty($book['book_id'])) {
                                        $editUrl = route('filament.admin.resources.books.edit', ['record' => $book['book_id']]);
                                        $html .= '<a href="' . $editUrl . '" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">View Book</a>';
                                    }
                                    $html .= '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody>';
                                $html .= '</table>';
                                $html .= '</div>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->html(),
                    ])
                    ->collapsed()
                    ->visible(function ($record) {
                        $log = $record->updated_log;
                        return !empty($log) && is_array($log) && count($log) > 0;
                    }),

                Infolists\Components\Section::make('Created Books Report')
                    ->description('Books that were newly created during import')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_books')
                            ->label('')
                            ->default(function ($record) {
                                $state = $record->created_log;
                                
                                if (empty($state) || !is_array($state)) {
                                    return 'No new books were created during this import.';
                                }

                                $totalBooks = count($state);
                                $html = '<div class="space-y-4">';

                                // Summary
                                $html .= '<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">';
                                $html .= '<div class="text-sm font-medium text-green-800 dark:text-green-200">Total Created: <span class="text-xl font-bold">' . $totalBooks . '</span></div>';
                                $html .= '</div>';

                                // Table
                                $html .= '<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">';
                                $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                                $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
                                $html .= '<tr>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Row #</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IDs</th>';
                                $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>';
                                $html .= '</tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($state as $book) {
                                    $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Row ' . ($book['row'] ?? 'Unknown') . '</span></td>';
                                    $html .= '<td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">' . e($book['title'] ?? 'Unknown') . '</td>';
                                    $html .= '<td class="px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">';
                                    if (!empty($book['internal_id'])) $html .= 'Internal: ' . e($book['internal_id']) . '<br>';
                                    if (!empty($book['palm_code'])) $html .= 'PALM: ' . e($book['palm_code']);
                                    $html .= '</td>';
                                    $html .= '<td class="px-6 py-4 text-sm">';
                                    if (!empty($book['book_id'])) {
                                        $editUrl = route('filament.admin.resources.books.edit', ['record' => $book['book_id']]);
                                        $html .= '<a href="' . $editUrl . '" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">View Book</a>';
                                    }
                                    $html .= '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody>';
                                $html .= '</table>';
                                $html .= '</div>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->html(),
                    ])
                    ->collapsed()
                    ->visible(function ($record) {
                        $log = $record->created_log;
                        return !empty($log) && is_array($log) && count($log) > 0;
                    }),

                Infolists\Components\Section::make('Failed Books Report')
                    ->description('Books that could not be imported from the CSV file')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_report')
                            ->label('')
                            ->view('filament.infolists.csv-import-error-report'),
                    ])
                    ->collapsed()
                    ->visible(function ($record) {
                        $log = $record->error_log;
                        return !empty($log) && is_array($log) && count($log) > 0;
                    }),

                Infolists\Components\Section::make('Validation Errors')
                    ->description('Errors found during CSV validation before import')
                    ->schema([
                        Infolists\Components\TextEntry::make('validation_errors_display')
                            ->label('')
                            ->default(function ($record) {
                                $state = $record->validation_errors;
                                
                                if (empty($state)) {
                                    return 'No validation errors';
                                }

                                if (is_array($state)) {
                                    $html = '<div class="space-y-2">';
                                    $html .= '<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">';
                                    $html .= '<ul class="list-disc list-inside space-y-1 text-sm text-red-800 dark:text-red-200">';
                                    foreach ($state as $error) {
                                        $html .= '<li>' . e($error) . '</li>';
                                    }
                                    $html .= '</ul>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    return new \Illuminate\Support\HtmlString($html);
                                }

                                return e($state);
                            })
                            ->html(),
                    ])
                    ->collapsed()
                    ->visible(fn ($record) => !empty($record->validation_errors)),
            ]);
    }
}
