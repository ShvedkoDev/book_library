<?php

namespace App\Filament\Resources\ClassificationTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->description('Books associated with any classification value of this type')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->limit(40)
                    ->toggleable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('authors.name')
                    ->label('Authors')
                    ->badge()
                    ->separator(',')
                    ->limit(30),

                Tables\Columns\TextColumn::make('languages.name')
                    ->label('Languages')
                    ->badge()
                    ->separator(',')
                    ->limit(20),

                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('access_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full' => 'success',
                        'limited' => 'warning',
                        'unavailable' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_level')
                    ->options([
                        'full' => 'Full Access',
                        'limited' => 'Limited Access',
                        'unavailable' => 'Unavailable',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('publication_year', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => route('filament.admin.resources.books.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
