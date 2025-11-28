<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\User;
use App\Models\BookReview;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent activity';
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookReview::query()
                    ->with(['book', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->limit(40)
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('review_text')
                    ->label('Review')
                    ->limit(60)
                    ->wrap(),
                    
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->action(function (BookReview $record) {
                        $record->update([
                            'is_approved' => true,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn (BookReview $record) => !$record->is_approved)
                    ->color('success'),
            ]);
    }
}
