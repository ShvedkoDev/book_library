<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SearchQueryResource\Pages;
use App\Filament\Resources\SearchQueryResource\RelationManagers;
use App\Models\SearchQuery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SearchQueryResource extends Resource
{
    protected static ?string $model = SearchQuery::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Search Analytics';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('query')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\TextInput::make('results_count')
                    ->disabled()
                    ->numeric(),
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
                Tables\Columns\TextColumn::make('query')
                    ->searchable()
                    ->sortable()
                    ->label('Search Query')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('results_count')
                    ->numeric()
                    ->sortable()
                    ->label('Results')
                    ->badge()
                    ->color(fn ($state) => $state === 0 ? 'danger' : 'success'),
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
                    ->label('Searched At')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\Filter::make('zero_results')
                    ->query(fn (Builder $query): Builder => $query->where('results_count', 0))
                    ->label('Zero Results'),
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
            'index' => Pages\ListSearchQueries::route('/'),
            'view' => Pages\ViewSearchQuery::route('/{record}'),
        ];
    }
}
