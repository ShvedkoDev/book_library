<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeographicLocationResource\Pages;
use App\Filament\Resources\GeographicLocationResource\RelationManagers;
use App\Models\GeographicLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GeographicLocationResource extends Resource
{
    protected static ?string $model = GeographicLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Geographic Locations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Location Details')
                    ->schema([
                        Forms\Components\Select::make('location_type')
                            ->required()
                            ->options([
                                'island' => 'Island',
                                'state' => 'State',
                                'region' => 'Region',
                            ])
                            ->native(false)
                            ->live()
                            ->placeholder('Select location type'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Chuuk State, Pohnpei'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Location (Optional)')
                            ->relationship('parent', 'name', function (Builder $query, Forms\Get $get) {
                                $query->whereIn('location_type', ['state', 'region']);
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Select parent location')
                            ->helperText('For islands, select the parent state'),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'state' => 'success',
                        'island' => 'info',
                        'region' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (GeographicLocation $record): ?string =>
                        $record->parent ? "Under: {$record->parent->name}" : null
                    ),

                Tables\Columns\TextColumn::make('books_count')
                    ->counts('bookLocations')
                    ->label('Books')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_type')
                    ->label('Type')
                    ->options([
                        'island' => 'Island',
                        'state' => 'State',
                        'region' => 'Region',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\Filter::make('has_parent')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id'))
                    ->label('Child locations only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('location_type')
            ->groups([
                Tables\Grouping\Group::make('location_type')
                    ->label('Location Type')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeographicLocations::route('/'),
            'create' => Pages\CreateGeographicLocation::route('/create'),
            'edit' => Pages\EditGeographicLocation::route('/{record}/edit'),
        ];
    }
}
