<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherResource\Pages;
use App\Filament\Resources\PublisherResource\RelationManagers;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('program_name')
                    ->label('Program/Project Name')
                    ->maxLength(255)
                    ->placeholder('e.g., Teacher Development Program')
                    ->helperText('Department, series, or specific program'),

                Forms\Components\Textarea::make('address')
                    ->rows(3),

                Forms\Components\TextInput::make('website')
                    ->url()
                    ->maxLength(255)
                    ->prefix('https://'),

                Forms\Components\TextInput::make('contact_email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('established_year')
                    ->numeric()
                    ->minValue(1000)
                    ->maxValue(date('Y')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('contact_email')->searchable(),
                Tables\Columns\TextColumn::make('established_year')->sortable(),
                Tables\Columns\TextColumn::make('books_count')
                    ->counts('books')
                    ->label('Books')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
        ];
    }
}
