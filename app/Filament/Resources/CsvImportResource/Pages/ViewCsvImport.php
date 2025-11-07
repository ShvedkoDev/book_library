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
                            ->label('File Name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Imported By'),
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
                                    ->label('Total Rows'),
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
                                Infolists\Components\TextEntry::make('getSuccessRate')
                                    ->label('Success Rate')
                                    ->formatStateUsing(fn ($record) => $record->getSuccessRate() . '%')
                                    ->color(fn ($record) => $record->getSuccessRate() >= 90 ? 'success' : ($record->getSuccessRate() >= 70 ? 'warning' : 'danger')),
                            ]),
                    ]),

                Infolists\Components\Section::make('Timing')
                    ->schema([
                        Infolists\Components\TextEntry::make('started_at')
                            ->dateTime()
                            ->label('Started At'),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->dateTime()
                            ->label('Completed At'),
                        Infolists\Components\TextEntry::make('duration_seconds')
                            ->label('Duration')
                            ->formatStateUsing(fn ($state) => $state ? round($state, 1) . ' seconds' : 'N/A'),
                    ])->columns(3),

                Infolists\Components\Section::make('Error Log')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_log')
                            ->label('')
                            ->formatStateUsing(function ($state, $record) {
                                if (empty($state)) {
                                    return 'No errors';
                                }

                                $errors = json_decode($state, true);
                                if (!is_array($errors)) {
                                    return $state;
                                }

                                $output = [];
                                foreach (array_slice($errors, 0, 20) as $error) {
                                    $row = $error['row'] ?? 'Unknown';
                                    $column = $error['column'] ?? 'N/A';
                                    $message = $error['message'] ?? 'Unknown error';
                                    $output[] = "Row {$row}, Column {$column}: {$message}";
                                }

                                if (count($errors) > 20) {
                                    $remaining = count($errors) - 20;
                                    $output[] = "\n... and {$remaining} more errors";
                                }

                                return implode("\n", $output);
                            })
                            ->markdown()
                            ->prose(),
                    ])
                    ->visible(fn ($record) => !empty($record->error_log)),
            ]);
    }
}
