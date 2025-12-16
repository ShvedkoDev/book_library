<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeopleResource\Pages;
use App\Filament\Resources\PeopleResource\RelationManagers;
use App\Models\Creator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PeopleResource extends Resource
{
    protected static ?string $model = Creator::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'People';
    protected static ?string $modelLabel = 'Person';
    protected static ?string $pluralModelLabel = 'People';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter person name')
                            ->columnSpanFull()
                            ->helperText('Full name of the person (author, illustrator, editor, etc.)'),

                        Forms\Components\Textarea::make('biography')
                            ->rows(4)
                            ->placeholder('Enter biography or brief description')
                            ->columnSpanFull()
                            ->helperText('Optional biographical information'),
                    ]),

                Forms\Components\Section::make('Additional Details')
                    ->description('Biographical information (optional - not required for local Micronesian contributors)')
                    ->schema([
                        Forms\Components\TextInput::make('birth_year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y'))
                            ->placeholder('e.g. 1950')
                            ->helperText('Leave empty if unknown'),

                        Forms\Components\TextInput::make('death_year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y'))
                            ->placeholder('e.g. 2020')
                            ->helperText('Leave empty if living or unknown'),

                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(100)
                            ->placeholder('e.g. Micronesian, Chuukese, Pohnpeian')
                            ->helperText('Cultural or national background'),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->prefix('https://'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Creator $record): ?string =>
                        $record->biography ? \Illuminate\Support\Str::limit($record->biography, 60) : null
                    ),

                Tables\Columns\TextColumn::make('roles')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(function (Creator $record) {
                        $roles = $record->bookCreators()
                            ->select('creator_type')
                            ->distinct()
                            ->pluck('creator_type')
                            ->map(fn ($type) => ucfirst($type))
                            ->toArray();

                        return empty($roles) ? ['No books'] : $roles;
                    })
                    ->color(fn (string $state): string => match($state) {
                        'Author' => 'primary',
                        'Illustrator' => 'success',
                        'Editor' => 'warning',
                        'Translator' => 'info',
                        'Contributor' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('bookCreators')
                            ->orderBy('book_creators_count', $direction);
                    }),

                Tables\Columns\TextColumn::make('books_count')
                    ->counts('bookCreators')
                    ->label('Books')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->tooltip('Total number of books this person contributed to'),

                // Hidden by default - can be toggled on
                Tables\Columns\TextColumn::make('nationality')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('birth_year')
                    ->numeric()
                    ->sortable()
                    ->label('Birth')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('death_year')
                    ->numeric()
                    ->sortable()
                    ->label('Death')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Living'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Added'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Updated'),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_books')
                    ->query(fn (Builder $query): Builder => $query->has('bookCreators'))
                    ->label('Has books')
                    ->toggle(),

                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'author' => 'Author',
                        'illustrator' => 'Illustrator',
                        'editor' => 'Editor',
                        'translator' => 'Translator',
                        'contributor' => 'Contributor',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            return $query->whereHas('bookCreators', function (Builder $q) use ($data) {
                                $q->where('creator_type', $data['value']);
                            });
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('nationality')
                    ->options(fn (): array =>
                        Creator::query()
                            ->whereNotNull('nationality')
                            ->distinct()
                            ->orderBy('nationality')
                            ->pluck('nationality', 'nationality')
                            ->toArray()
                    )
                    ->searchable(),

                Tables\Filters\Filter::make('micronesian')
                    ->label('Micronesian contributors')
                    ->query(fn (Builder $query): Builder =>
                        $query->where(function ($q) {
                            $q->where('nationality', 'like', '%Micronesian%')
                              ->orWhere('nationality', 'like', '%Chuukese%')
                              ->orWhere('nationality', 'like', '%Pohnpeian%')
                              ->orWhere('nationality', 'like', '%Yapese%')
                              ->orWhere('nationality', 'like', '%Kosraean%')
                              ->orWhere('nationality', 'like', '%Marshallese%')
                              ->orWhere('nationality', 'like', '%Palauan%');
                        })
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('merge')
                    ->label('Merge')
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('warning')
                    ->modalHeading('Merge Person')
                    ->modalDescription('Merge this person into another record. This action cannot be undone.')
                    ->form([
                        Forms\Components\Placeholder::make('warning')
                            ->label('Warning')
                            ->content('You are about to merge this person into another record. All book associations will be moved to the selected target person, and this record will be PERMANENTLY DELETED.')
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('source_person')
                            ->label('Person being merged (will be deleted)')
                            ->content(fn (Creator $record) => $record->name)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('target_id')
                            ->label('Merge into (Target person)')
                            ->options(fn (Creator $record) => Creator::where('id', '!=', $record->id)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Select the person record that should be kept. All books will be moved to this person.')
                            ->columnSpanFull(),
                    ])
                    ->action(function (Creator $record, array $data) {
                        $target = Creator::find($data['target_id']);
                        
                        if (!$target) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Target person not found')
                                ->send();
                            return;
                        }

                        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $target) {
                            // Get all book relationships for the source creator
                            $sourceRelationships = \Illuminate\Support\Facades\DB::table('book_creators')
                                ->where('creator_id', $record->id)
                                ->get();

                            $movedCount = 0;
                            $skippedCount = 0;

                            foreach ($sourceRelationships as $rel) {
                                // Check if the target creator already has a relationship with this book and role
                                $exists = \Illuminate\Support\Facades\DB::table('book_creators')
                                    ->where('book_id', $rel->book_id)
                                    ->where('creator_id', $target->id)
                                    ->where('creator_type', $rel->creator_type)
                                    ->exists();

                                if ($exists) {
                                    // If relationship already exists, just delete the source relationship (it's a duplicate)
                                    \Illuminate\Support\Facades\DB::table('book_creators')->where('id', $rel->id)->delete();
                                    $skippedCount++;
                                } else {
                                    // Move relationship to target
                                    \Illuminate\Support\Facades\DB::table('book_creators')
                                        ->where('id', $rel->id)
                                        ->update(['creator_id' => $target->id]);
                                    $movedCount++;
                                }
                            }

                            // Delete the source creator
                            $record->delete();

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('People merged successfully')
                                ->body("Merged '{$record->name}' into '{$target->name}'. Moved {$movedCount} books, skipped {$skippedCount} duplicates.")
                                ->persistent()
                                ->send();
                        });
                    })
                    ->requiresConfirmation()
                    ->modalSubmitActionLabel('Merge People'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No people yet')
            ->emptyStateDescription('Start by adding authors, illustrators, editors, and other contributors.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add person'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
