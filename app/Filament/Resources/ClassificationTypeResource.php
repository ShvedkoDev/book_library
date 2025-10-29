<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassificationTypeResource\Pages;
use App\Filament\Resources\ClassificationTypeResource\RelationManagers;
use App\Models\ClassificationType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ClassificationTypeResource extends Resource
{
    protected static ?string $model = ClassificationType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Classification Types';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Purpose, Genre, Type')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state))
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('auto-generated from name')
                            ->helperText('Used in URLs and system references'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Describe what this classification type represents')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('allow_multiple')
                            ->label('Allow Multiple Values')
                            ->helperText('Can a book have multiple values for this type?')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('use_for_filtering')
                            ->label('Use in Search Filters')
                            ->helperText('Show this classification in library filters?')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive types are hidden from users')
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (ClassificationType $record): ?string => $record->slug),

                Tables\Columns\TextColumn::make('classification_values_count')
                    ->counts('classificationValues')
                    ->label('Values')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('allow_multiple')
                    ->label('Multi')
                    ->boolean()
                    ->tooltip(fn ($state) => $state ? 'Allows multiple values' : 'Single value only'),

                Tables\Columns\IconColumn::make('use_for_filtering')
                    ->label('Filter')
                    ->boolean()
                    ->tooltip(fn ($state) => $state ? 'Used in search filters' : 'Not in filters'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All types')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\TernaryFilter::make('use_for_filtering')
                    ->label('Used in Filters'),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
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
            'index' => Pages\ListClassificationTypes::route('/'),
            'create' => Pages\CreateClassificationType::route('/create'),
            'edit' => Pages\EditClassificationType::route('/{record}/edit'),
        ];
    }
}
