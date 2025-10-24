<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookViewResource\Pages;
use App\Filament\Resources\BookViewResource\RelationManagers;
use App\Models\BookView;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

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
            ->modifyQueryUsing(function (Builder $query) {
                // Group views by book and count them
                $query->select([
                    'book_views.book_id',
                    DB::raw('COUNT(*) as view_count'),
                    DB::raw('MAX(book_views.created_at) as latest_view'),
                    DB::raw('MIN(book_views.created_at) as first_view'),
                ])
                ->groupBy('book_views.book_id')
                ->orderByDesc('view_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->searchable()
                    ->sortable()
                    ->label('Book Title')
                    ->weight('bold')
                    ->url(fn ($record) => BookViewResource::getUrl('details', ['bookId' => $record->book_id])),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Total Views')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('latest_view')
                    ->dateTime()
                    ->sortable()
                    ->label('Latest View')
                    ->since(),
                Tables\Columns\TextColumn::make('first_view')
                    ->dateTime()
                    ->sortable()
                    ->label('First View')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_views.created_at)'), '>=', now()->subHours(24)))
                    ->label('Last 24 Hours'),
                Tables\Filters\Filter::make('last_7_days')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_views.created_at)'), '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),
                Tables\Filters\Filter::make('last_30_days')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_views.created_at)'), '>=', now()->subDays(30)))
                    ->label('Last 30 Days'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => BookViewResource::getUrl('details', ['bookId' => $record->book_id])),
            ])
            ->bulkActions([
                // No bulk actions needed for grouped data
            ])
            ->defaultSort('view_count', 'desc');
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
            'details' => Pages\BookViewDetails::route('/{bookId}/details'),
        ];
    }
}
