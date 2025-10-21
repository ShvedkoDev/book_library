<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularBooksWidget extends BaseWidget
{
    protected static ?string $heading = 'Most Popular Books';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()
                    ->withCount(['ratings', 'downloads' => function ($query) {
                        $query->where('created_at', '>=', now()->subDays(30));
                    }])
                    ->withAvg('ratings', 'rating')
                    ->orderByDesc('downloads_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Book Title')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('languages.name')
                    ->label('Language')
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads (30d)')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('ratings_avg_rating')
                    ->label('Avg Rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '/5' : 'No ratings')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ratings_count')
                    ->label('Total Ratings')
                    ->sortable(),
                    
                Tables\Columns\SelectColumn::make('access_level')
                    ->options([
                        'full' => 'Full',
                        'limited' => 'Limited',
                        'unavailable' => 'Unavailable'
                    ]),
            ]);
    }
}
