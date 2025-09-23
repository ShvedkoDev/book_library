<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookDownloadResource\Pages;
use App\Models\BookDownload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookDownloadResource extends Resource
{
    protected static ?string $model = BookDownload::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
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
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(45),
                    
                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->rows(2),
                    
                Forms\Components\DateTimePicker::make('downloaded_at')
                    ->label('Downloaded At')
                    ->default(now()),
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
                    ->sortable()
                    ->placeholder('Anonymous'),
                    
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('downloaded_at')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('downloaded_today')
                    ->query(fn ($query) => $query->whereDate('downloaded_at', today()))
                    ->label('Downloaded Today'),
                    
                Tables\Filters\Filter::make('downloaded_this_week')
                    ->query(fn ($query) => $query->where('downloaded_at', '>=', now()->startOfWeek()))
                    ->label('Downloaded This Week'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('downloaded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookDownloads::route('/'),
            'create' => Pages\CreateBookDownload::route('/create'),
            'edit' => Pages\EditBookDownload::route('/{record}/edit'),
        ];
    }
}
