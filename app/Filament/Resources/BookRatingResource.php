<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookRatingResource\Pages;
use App\Models\BookRating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookRatingResource extends Resource
{
    protected static ?string $model = BookRating::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = "Library";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars'
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'danger',
                        '2' => 'warning',
                        '3' => 'gray',
                        '4' => 'info',
                        '5' => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state . ' Star' . ($state > 1 ? 's' : ''))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars'
                    ]),
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
            'index' => Pages\ListBookRatings::route('/'),
            'create' => Pages\CreateBookRating::route('/create'),
            'edit' => Pages\EditBookRating::route('/{record}/edit'),
        ];
    }
}
