<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\ClassificationValue;
use App\Models\ClassificationType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = ClassificationValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Library";

    protected static ?string $label = 'Category';
    protected static ?string $pluralLabel = 'Categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('classification_type_id')
                    ->label('Classification Type')
                    ->relationship('classificationType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    ->required()
                    ->maxLength(100),

                Forms\Components\Textarea::make('description')
                    ->rows(3),

                Forms\Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'value')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classificationType.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.value')
                    ->label('Parent Category')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classification_type_id')
                    ->label('Type')
                    ->relationship('classificationType', 'name'),

                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('classification_type_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
