<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookViewResource\Pages;
use App\Filament\Resources\BookViewResource\RelationManagers;
use App\Models\BookView;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookViewResource extends Resource
{
    protected static ?string $model = BookView::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationLabel = 'Book Views';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('book.title')
                    ->disabled()
                    ->label('Book'),
                Forms\Components\TextInput::make('user.name')
                    ->disabled()
                    ->label('User'),
                Forms\Components\TextInput::make('ip_address')
                    ->disabled()
                    ->maxLength(45),
                Forms\Components\TextInput::make('user_agent')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('created_at')
                    ->disabled(),
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
                Tables\Columns\TextColumn::make('book.title')
                    ->searchable()
                    ->sortable()
                    ->label('Book Title')
                    ->weight('bold')
                    ->url(fn ($record) => $record->book ? route('library.show', $record->book->slug) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('book.view_count')
                    ->label('Total Views')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('User')
                    ->toggleable()
                    ->placeholder('Guest'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Viewed At')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subHours(24)))
                    ->label('Last 24 Hours'),
                Tables\Filters\Filter::make('last_7_days')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),
                Tables\Filters\Filter::make('last_30_days')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Last 30 Days'),
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
            'index' => Pages\ListBookViews::route('/'),
            'view' => Pages\ViewBookView::route('/{record}'),
        ];
    }
}
