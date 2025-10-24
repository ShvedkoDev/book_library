<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterAnalyticResource\Pages;
use App\Filament\Resources\FilterAnalyticResource\RelationManagers;
use App\Models\FilterAnalytic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilterAnalyticResource extends Resource
{
    protected static ?string $model = FilterAnalytic::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Filter Analytics';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralLabel = 'Filter Analytics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('filter_type')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\TextInput::make('filter_value')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\TextInput::make('filter_slug')
                    ->disabled()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('filter_type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Filter Type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('filter_value')
                    ->searchable()
                    ->sortable()
                    ->label('Filter Value')
                    ->weight('bold'),
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
                    ->label('Used At')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('filter_type')
                    ->label('Filter Type')
                    ->options([
                        'subjects' => 'Subjects',
                        'grades' => 'Grade Levels',
                        'types' => 'Resource Types',
                        'languages' => 'Languages',
                        'years' => 'Publication Years',
                    ]),
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
            'index' => Pages\ListFilterAnalytics::route('/'),
            'view' => Pages\ViewFilterAnalytic::route('/{record}'),
        ];
    }
}
