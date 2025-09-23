<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Models\Author;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = "Library";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('biography')
                    ->rows(4),
                    
                Forms\Components\TextInput::make('birth_year')
                    ->numeric()
                    ->minValue(1000)
                    ->maxValue(date('Y')),
                    
                Forms\Components\TextInput::make('death_year')
                    ->numeric()
                    ->minValue(1000)
                    ->maxValue(date('Y')),
                    
                Forms\Components\TextInput::make('nationality')
                    ->maxLength(100),
                    
                Forms\Components\TextInput::make('website')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nationality')->sortable(),
                Tables\Columns\TextColumn::make('birth_year')->sortable(),
                Tables\Columns\TextColumn::make('death_year')->sortable(),
                Tables\Columns\TextColumn::make('books_count')
                    ->counts('books')
                    ->label('Books Count'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nationality')
                    ->options(Author::distinct('nationality')->pluck('nationality', 'nationality')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
