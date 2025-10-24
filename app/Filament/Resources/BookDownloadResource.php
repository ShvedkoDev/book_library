<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookDownloadResource\Pages;
use App\Filament\Resources\BookDownloadResource\RelationManagers;
use App\Models\BookDownload;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BookDownloadResource extends Resource
{
    protected static ?string $model = BookDownload::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Book Downloads';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 4;

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
                // Group downloads by book and count them
                $query->select([
                    'book_downloads.book_id',
                    DB::raw('COUNT(*) as download_count'),
                    DB::raw('MAX(book_downloads.created_at) as latest_download'),
                    DB::raw('MIN(book_downloads.created_at) as first_download'),
                ])
                ->groupBy('book_downloads.book_id')
                ->orderByDesc('download_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->searchable()
                    ->sortable()
                    ->label('Book Title')
                    ->weight('bold')
                    ->url(fn ($record) => BookDownloadResource::getUrl('details', ['bookId' => $record->book_id])),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Total Downloads')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('latest_download')
                    ->dateTime()
                    ->sortable()
                    ->label('Latest Download')
                    ->since(),
                Tables\Columns\TextColumn::make('first_download')
                    ->dateTime()
                    ->sortable()
                    ->label('First Download')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_downloads.created_at)'), '>=', now()->subHours(24)))
                    ->label('Last 24 Hours'),
                Tables\Filters\Filter::make('last_7_days')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_downloads.created_at)'), '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),
                Tables\Filters\Filter::make('last_30_days')
                    ->query(fn (Builder $query): Builder => $query->having(DB::raw('MAX(book_downloads.created_at)'), '>=', now()->subDays(30)))
                    ->label('Last 30 Days'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => BookDownloadResource::getUrl('details', ['bookId' => $record->book_id])),
            ])
            ->bulkActions([
                // No bulk actions needed for grouped data
            ])
            ->defaultSort('download_count', 'desc');
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
            'index' => Pages\ListBookDownloads::route('/'),
            'details' => Pages\BookDownloadDetails::route('/{bookId}/details'),
        ];
    }
}
