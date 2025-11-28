<?php

namespace App\Filament\Resources\AuthorResource\RelationManagers;

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

                Tables\Columns\TextColumn::make('pivot.creator_type')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn ($state) => match ($state) {
                        'author' => 'success',
                        'illustrator' => 'info',
                        'editor' => 'warning',
                        'translator' => 'primary',
                        'contributor' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pivot.role_description')
                    ->label('Role description')
                    ->placeholder('-')
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('creator_type')
                    ->options([
                        'author' => 'Author',
                        'illustrator' => 'Illustrator',
                        'editor' => 'Editor',
                        'translator' => 'Translator',
                        'contributor' => 'Contributor',
                    ])
                    ->attribute('pivot.creator_type')
                    ->label('Role as'),

                Tables\Filters\SelectFilter::make('access_level')
                    ->options([
                        'full' => 'Full Access',
                        'limited' => 'Limited Access',
                        'unavailable' => 'Unavailable',
                    ]),
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
