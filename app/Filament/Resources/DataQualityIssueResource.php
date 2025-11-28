<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataQualityIssueResource\Pages;
use App\Models\DataQualityIssue;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class DataQualityIssueResource extends Resource
{
    protected static ?string $model = DataQualityIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Data quality issues';

    protected static ?string $navigationGroup = 'CSV Import/Export';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'message';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::unresolved()->critical()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Issue Information')
                    ->schema([
                        Forms\Components\TextInput::make('book_id')
                            ->label('Book ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('book.title')
                            ->label('Book title')
                            ->disabled(),

                        Forms\Components\Select::make('issue_type')
                            ->disabled()
                            ->label('Issue type'),

                        Forms\Components\Select::make('severity')
                            ->disabled()
                            ->options([
                                'critical' => 'Critical',
                                'warning' => 'Warning',
                                'info' => 'Info',
                            ]),

                        Forms\Components\TextInput::make('field_name')
                            ->disabled()
                            ->label('Field name'),

                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->label('Message')
                            ->rows(3),

                        Forms\Components\KeyValue::make('context')
                            ->disabled()
                            ->label('Context')
                            ->visible(fn ($record) => !empty($record->context)),
                    ])->columns(2),

                Forms\Components\Section::make('Resolution')
                    ->schema([
                        Forms\Components\Toggle::make('is_resolved')
                            ->label('Resolved')
                            ->reactive(),

                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->disabled()
                            ->label('Resolved at')
                            ->visible(fn ($get) => $get('is_resolved')),

                        Forms\Components\TextInput::make('resolvedBy.name')
                            ->disabled()
                            ->label('Resolved by')
                            ->visible(fn ($get) => $get('is_resolved')),

                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution notes')
                            ->rows(3)
                            ->visible(fn ($get) => $get('is_resolved')),
                    ])->columns(2),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('csvImport.filename')
                            ->disabled()
                            ->label('From CSV import')
                            ->visible(fn ($record) => $record->csv_import_id),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled()
                            ->label('Detected at'),
                    ])->columns(2),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return !$record->is_resolved; // Only allow editing unresolved issues
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'warning',
                        'info' => 'info',
                    ])
                    ->icons([
                        'heroicon-o-exclamation-circle' => 'critical',
                        'heroicon-o-exclamation-triangle' => 'warning',
                        'heroicon-o-information-circle' => 'info',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('issue_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                    ->wrap(),

                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    })
                    ->url(fn (DataQualityIssue $record): string => $record->book
                        ? route('filament.admin.resources.books.edit', ['record' => $record->book_id])
                        : '#'),

                Tables\Columns\TextColumn::make('field_name')
                    ->label('Field')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    })
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('Resolved')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Detected')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('csvImport.filename')
                    ->label('Import')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(30)
                    ->url(fn (DataQualityIssue $record): ?string => $record->csv_import_id
                        ? route('filament.admin.resources.csv-imports.view', ['record' => $record->csv_import_id])
                        : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'critical' => 'Critical',
                        'warning' => 'Warning',
                        'info' => 'Info',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('issue_type')
                    ->options(function () {
                        return DataQualityIssue::select('issue_type')
                            ->distinct()
                            ->pluck('issue_type', 'issue_type')
                            ->mapWithKeys(fn ($type) => [
                                $type => str_replace('_', ' ', ucwords($type, '_'))
                            ])
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_resolved')
                    ->label('Resolution status')
                    ->placeholder('All issues')
                    ->trueLabel('Resolved')
                    ->falseLabel('Unresolved')
                    ->default(false), // Default to unresolved

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Detected from'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Detected until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('csv_import_id')
                    ->label('CSV import')
                    ->relationship('csvImport', 'filename')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('view_book')
                    ->label('View book')
                    ->icon('heroicon-o-book-open')
                    ->url(fn (DataQualityIssue $record): string => $record->book
                        ? route('filament.admin.resources.books.edit', ['record' => $record->book_id])
                        : '#')
                    ->openUrlInNewTab()
                    ->visible(fn (DataQualityIssue $record) => $record->book_id),

                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution notes')
                            ->rows(3)
                            ->placeholder('Optionally add notes about how this issue was resolved...'),
                    ])
                    ->action(function (DataQualityIssue $record, array $data): void {
                        $record->markAsResolved($data['resolution_notes'] ?? null);

                        Notification::make()
                            ->title('Issue Resolved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (DataQualityIssue $record) => !$record->is_resolved),

                Tables\Actions\Action::make('unresolve')
                    ->label('Mark as unresolved')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (DataQualityIssue $record): void {
                        $record->update([
                            'is_resolved' => false,
                            'resolved_at' => null,
                            'resolved_by' => null,
                        ]);

                        Notification::make()
                            ->title('Issue Marked as Unresolved')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (DataQualityIssue $record) => $record->is_resolved),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (DataQualityIssue $record) => $record->is_resolved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('resolve_selected')
                        ->label('Resolve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('resolution_notes')
                                ->label('Resolution notes')
                                ->rows(3)
                                ->placeholder('Optionally add notes about how these issues were resolved...'),
                        ])
                        ->action(function ($records, array $data): void {
                            foreach ($records as $record) {
                                if (!$record->is_resolved) {
                                    $record->markAsResolved($data['resolution_notes'] ?? null);
                                }
                            }

                            Notification::make()
                                ->title('Issues Resolved')
                                ->body(count($records) . ' issue(s) marked as resolved.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Only resolved issues can be deleted.'),
                ]),
            ])
            ->emptyStateHeading('No Data Quality Issues')
            ->emptyStateDescription('No data quality issues have been detected yet.')
            ->emptyStateIcon('heroicon-o-check-circle');
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
            'index' => Pages\ListDataQualityIssues::route('/'),
            'view' => Pages\ViewDataQualityIssue::route('/{record}'),
        ];
    }
}
