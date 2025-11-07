<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CsvImportResource\Pages;
use App\Models\CsvImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class CsvImportResource extends Resource
{
    protected static ?string $model = CsvImport::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Import History';

    protected static ?string $navigationGroup = 'CSV Import/Export';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Information')
                    ->schema([
                        Forms\Components\TextInput::make('filename')
                            ->disabled()
                            ->label('File Name'),
                        Forms\Components\TextInput::make('user.name')
                            ->disabled()
                            ->label('Imported By'),
                        Forms\Components\Select::make('mode')
                            ->disabled()
                            ->options([
                                'create_only' => 'Create Only',
                                'update_only' => 'Update Only',
                                'upsert' => 'Upsert',
                                'create_duplicates' => 'Create Duplicates',
                            ]),
                        Forms\Components\Select::make('status')
                            ->disabled()
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('total_rows')
                            ->disabled()
                            ->label('Total Rows')
                            ->numeric(),
                        Forms\Components\TextInput::make('processed_rows')
                            ->disabled()
                            ->label('Processed')
                            ->numeric(),
                        Forms\Components\TextInput::make('successful_rows')
                            ->disabled()
                            ->label('Successful')
                            ->numeric(),
                        Forms\Components\TextInput::make('failed_rows')
                            ->disabled()
                            ->label('Failed')
                            ->numeric(),
                        Forms\Components\TextInput::make('created_count')
                            ->disabled()
                            ->label('Created')
                            ->numeric(),
                        Forms\Components\TextInput::make('updated_count')
                            ->disabled()
                            ->label('Updated')
                            ->numeric(),
                    ])->columns(3),

                Forms\Components\Section::make('Timing')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled(),
                        Forms\Components\TextInput::make('duration_seconds')
                            ->disabled()
                            ->label('Duration (seconds)')
                            ->numeric()
                            ->suffix('s'),
                    ])->columns(3),

                Forms\Components\Section::make('Error Log')
                    ->schema([
                        Forms\Components\Textarea::make('error_log')
                            ->disabled()
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->error_log)),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->searchable()
                    ->sortable()
                    ->label('File Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Imported By'),

                Tables\Columns\TextColumn::make('mode')
                    ->badge()
                    ->colors([
                        'info' => 'create_only',
                        'warning' => 'update_only',
                        'success' => 'upsert',
                        'danger' => 'create_duplicates',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'create_only' => 'Create Only',
                        'update_only' => 'Update Only',
                        'upsert' => 'Upsert',
                        'create_duplicates' => 'Create Duplicates',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('total_rows')
                    ->numeric()
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\TextColumn::make('successful_rows')
                    ->numeric()
                    ->label('Success')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('failed_rows')
                    ->numeric()
                    ->label('Failed')
                    ->sortable()
                    ->color('danger')
                    ->badge()
                    ->visible(fn ($record) => $record->failed_rows > 0),

                Tables\Columns\TextColumn::make('created_count')
                    ->numeric()
                    ->label('Created')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_count')
                    ->numeric()
                    ->label('Updated')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Started')
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->numeric()
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? round($state, 1) . 's' : '-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'create_only' => 'Create Only',
                        'update_only' => 'Update Only',
                        'upsert' => 'Upsert',
                        'create_duplicates' => 'Create Duplicates',
                    ]),

                Tables\Filters\Filter::make('with_errors')
                    ->label('Has Errors')
                    ->query(fn (Builder $query): Builder => $query->where('failed_rows', '>', 0)),

                Tables\Filters\Filter::make('recent')
                    ->label('Last 7 Days')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCsvImports::route('/'),
            'view' => Pages\ViewCsvImport::route('/{record}'),
        ];
    }
}
