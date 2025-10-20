<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\Collection;
use App\Models\Author;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Library';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identifiers')
                    ->schema([
                        Forms\Components\TextInput::make('internal_id')
                            ->label('Internal ID')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Auto-generated or manual ID')
                            ->helperText('CSV: ID - Internal unique identifier'),

                        Forms\Components\TextInput::make('palm_code')
                            ->label('PALM Code')
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->placeholder('PALM catalog code')
                            ->helperText('CSV: PALM code'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Book title')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('subtitle')
                            ->maxLength(500)
                            ->placeholder('Book subtitle')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('translated_title')
                            ->label('Translated Title')
                            ->maxLength(500)
                            ->placeholder('Title in another language')
                            ->helperText('CSV: Translated-title')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('physical_type')
                            ->label('Physical Type')
                            ->options([
                                'book' => 'Book',
                                'journal' => 'Journal',
                                'magazine' => 'Magazine',
                                'workbook' => 'Workbook',
                                'poster' => 'Poster',
                                'other' => 'Other',
                            ])
                            ->native(false)
                            ->placeholder('Select type')
                            ->helperText('CSV: Physical type'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing Details')
                    ->schema([
                        Forms\Components\Select::make('publisher_id')
                            ->label('Publisher')
                            ->relationship('publisher', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),

                        Forms\Components\Select::make('collection_id')
                            ->label('Collection')
                            ->relationship('collection', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),

                        Forms\Components\TextInput::make('publication_year')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(date('Y'))
                            ->placeholder('YYYY')
                            ->helperText('CSV: Year'),

                        Forms\Components\TextInput::make('pages')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Number of pages')
                            ->helperText('CSV: Pages'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Content & Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Abstract / Description')
                            ->rows(4)
                            ->placeholder('Brief description of the book')
                            ->helperText('CSV: ABSTRACT/DESCRIPTION')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('toc')
                            ->label('Table of Contents')
                            ->rows(4)
                            ->placeholder('List of chapters or sections')
                            ->helperText('CSV: TOC')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes_issue')
                            ->label('Notes - Issue')
                            ->rows(3)
                            ->placeholder('Notes related to the issue/edition')
                            ->helperText('CSV: Notes related to the issue'),

                        Forms\Components\Textarea::make('notes_content')
                            ->label('Notes - Content')
                            ->rows(3)
                            ->placeholder('Notes about the content')
                            ->helperText('CSV: Notes related to content'),

                        Forms\Components\Textarea::make('contact')
                            ->label('Contact / Ordering Info')
                            ->rows(2)
                            ->placeholder('Hard copy ordering information')
                            ->helperText('CSV: CONTACT - Hard copy ordering info')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Educational Standards')
                    ->schema([
                        Forms\Components\TextInput::make('vla_standard')
                            ->label('VLA Standard')
                            ->maxLength(255)
                            ->placeholder('Vernacular Literacy Assessment standard')
                            ->helperText('CSV: VLA standard'),

                        Forms\Components\TextInput::make('vla_benchmark')
                            ->label('VLA Benchmark')
                            ->maxLength(255)
                            ->placeholder('Vernacular Literacy Assessment benchmark')
                            ->helperText('CSV: VLA benchmark'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Access & Settings')
                    ->schema([
                        Forms\Components\Select::make('access_level')
                            ->label('Access Level')
                            ->options([
                                'full' => 'Full Access',
                                'limited' => 'Limited Access',
                                'unavailable' => 'Unavailable',
                            ])
                            ->default('unavailable')
                            ->required()
                            ->native(false)
                            ->helperText('CSV: UPLOADED'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Show on featured books list'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Visible in library'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('languages')
                            ->relationship('languages', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select one or more languages'),

                        Forms\Components\Select::make('creators')
                            ->relationship('creators', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ])
                            ->helperText('Authors, illustrators, translators'),

                        Forms\Components\Select::make('geographicLocations')
                            ->label('Geographic Locations')
                            ->relationship('geographicLocations', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Islands and states'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('internal_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('palm_code')
                    ->label('PALM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('creators.name')
                    ->badge()
                    ->separator(',')
                    ->limit(30)
                    ->label('Creators'),

                Tables\Columns\TextColumn::make('languages.name')
                    ->badge()
                    ->separator(',')
                    ->label('Languages'),

                Tables\Columns\TextColumn::make('physical_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'book' => 'success',
                        'journal' => 'info',
                        'magazine' => 'warning',
                        'workbook' => 'primary',
                        'poster' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('publisher.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('collection.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Year')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\SelectColumn::make('access_level')
                    ->options([
                        'full' => 'Full',
                        'limited' => 'Limited',
                        'unavailable' => 'Unavailable'
                    ]),

                Tables\Columns\ToggleColumn::make('is_featured'),
                Tables\Columns\ToggleColumn::make('is_active'),

                Tables\Columns\TextColumn::make('view_count')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('languages')
                    ->relationship('languages', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('publisher')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('access_level')
                    ->options([
                        'full' => 'Full Access',
                        'limited' => 'Limited Access',
                        'unavailable' => 'Unavailable'
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
