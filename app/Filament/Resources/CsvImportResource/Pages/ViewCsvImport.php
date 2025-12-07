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
                                    ->formatStateUsing(fn ($state) => $state . '%')
                                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 70 ? 'warning' : 'danger')),
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

                Infolists\Components\Section::make('Failed Books Report')
                    ->description('Books that could not be imported from the CSV file')
                    ->schema([
                        Infolists\Components\ViewEntry::make('error_report')
                            ->label('')
                            ->view('filament.infolists.csv-import-error-report'),
                    ])
                    ->collapsed()
                    ->visible(fn ($record) => !empty($record->error_log) && is_array($record->error_log) && count($record->error_log) > 0),

                Infolists\Components\Section::make('Validation Errors')
                    ->description('Errors found during CSV validation before import')
                    ->schema([
                        Infolists\Components\TextEntry::make('validation_errors')
                            ->label('')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return 'No validation errors';
                                }

                                if (is_array($state)) {
                                    return implode("\n", array_map(fn($error) => "• {$error}", $state));
                                }

                                return $state;
                            })
                            ->markdown()
                            ->prose(),
                    ])
                    ->collapsed()
                    ->visible(fn ($record) => !empty($record->validation_errors)),
            ]);
    }
}
