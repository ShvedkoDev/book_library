<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassificationValueResource\Pages;
use App\Filament\Resources\ClassificationValueResource\RelationManagers;
use App\Models\ClassificationValue;
use App\Models\ClassificationType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassificationValueResource extends Resource
{
    protected static ?string $model = ClassificationValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Classification Values';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Classification Details')
                    ->schema([
                        Forms\Components\Select::make('classification_type_id')
                            ->label('Classification Type')
                            ->relationship('classificationType', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->placeholder('Select a classification type'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Value (Optional)')
                            ->relationship('parent', 'value', function (Builder $query, Forms\Get $get) {
                                $typeId = $get('classification_type_id');
                                if ($typeId) {
                                    $query->where('classification_type_id', $typeId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Select parent if this is a sub-category')
                            ->helperText('For hierarchical classifications (e.g., Sub-genre under Genre)'),

                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Fiction, Science, Elementary')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of this classification value')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive values are hidden from users'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classificationType.name')
                    ->label('Type')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('value')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (ClassificationValue $record): ?string =>
                        $record->parent ? "Under: {$record->parent->value}" : null
                    ),

                Tables\Columns\TextColumn::make('books_count')
                    ->counts('bookClassifications')
                    ->label('Books')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classification_type_id')
                    ->label('Type')
                    ->relationship('classificationType', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All values')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('has_parent')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id'))
                    ->label('Sub-categories only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('classificationType.name')
            ->groups([
                Tables\Grouping\Group::make('classificationType.name')
                    ->label('Classification Type')
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListClassificationValues::route('/'),
            'create' => Pages\CreateClassificationValue::route('/create'),
            'edit' => Pages\EditClassificationValue::route('/{record}/edit'),
        ];
    }
}
