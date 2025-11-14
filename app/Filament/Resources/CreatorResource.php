<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorResource\Pages;
use App\Filament\Resources\CreatorResource\RelationManagers;
use App\Models\Creator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreatorResource extends Resource
{
    protected static ?string $model = Creator::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 3;

    // DEPRECATED: This resource has been replaced by PeopleResource
    // Kept for backward compatibility only
    protected static bool $shouldRegisterNavigation = false;

    // Enable global search
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'biography', 'nationality'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Nationality' => $record->nationality,
            'Books' => $record->bookCreators()->count(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter creator name')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('biography')
                            ->rows(4)
                            ->placeholder('Enter biography or brief description')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Additional Details')
                    ->schema([
                        Forms\Components\TextInput::make('birth_year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y'))
                            ->placeholder('e.g. 1950'),

                        Forms\Components\TextInput::make('death_year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y'))
                            ->placeholder('e.g. 2020')
                            ->hint('Leave empty if still living'),

                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(100)
                            ->placeholder('e.g. Micronesian, Chuukese'),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->prefix('https://'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Creator $record): ?string => $record->nationality),

                Tables\Columns\TextColumn::make('birth_year')
                    ->numeric()
                    ->sortable()
                    ->label('Birth')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('death_year')
                    ->numeric()
                    ->sortable()
                    ->label('Death')
                    ->placeholder('Living'),

                Tables\Columns\TextColumn::make('books_count')
                    ->counts('bookCreators')
                    ->label('Books')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_books')
                    ->query(fn (Builder $query): Builder => $query->has('bookCreators'))
                    ->label('Has Books'),

                Tables\Filters\SelectFilter::make('nationality')
                    ->options(fn (): array =>
                        Creator::query()
                            ->whereNotNull('nationality')
                            ->distinct()
                            ->pluck('nationality', 'nationality')
                            ->toArray()
                    ),
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
            ->defaultSort('name');
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
            'index' => Pages\ListCreators::route('/'),
            'create' => Pages\CreateCreator::route('/create'),
            'edit' => Pages\EditCreator::route('/{record}/edit'),
        ];
    }
}
