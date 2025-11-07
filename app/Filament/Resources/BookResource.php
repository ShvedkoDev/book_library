<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\Collection;
use App\Models\Creator;
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
                Forms\Components\Placeholder::make('duplication_info')
                    ->label('Duplication Information')
                    ->content(fn ($record) => $record && $record->isDuplicate()
                        ? view('filament.components.duplication-info', [
                            'record' => $record,
                            'sourceBook' => $record->duplicatedFrom,
                            'duplicatedAt' => $record->duplicated_at,
                        ])
                        : null
                    )
                    ->visible(fn ($record) => $record && $record->isDuplicate())
                    ->columnSpanFull(),

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

                Forms\Components\Section::make('Classifications')
                    ->schema([
                        Forms\Components\Select::make('purposeClassifications')
                            ->label('Purpose')
                            ->relationship(
                                'purposeClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'purpose');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Purpose'),

                        Forms\Components\Select::make('genreClassifications')
                            ->label('Genre')
                            ->relationship(
                                'genreClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'genre');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Genre'),

                        Forms\Components\Select::make('subgenreClassifications')
                            ->label('Sub-genre')
                            ->relationship(
                                'subgenreClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'sub-genre');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Sub-genre'),

                        Forms\Components\Select::make('typeClassifications')
                            ->label('Type')
                            ->relationship(
                                'typeClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'type');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Type'),

                        Forms\Components\Select::make('themesClassifications')
                            ->label('Themes/Uses')
                            ->relationship(
                                'themesClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'themes-uses');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Themes/Uses'),

                        Forms\Components\Select::make('learnerLevelClassifications')
                            ->label('Learner Level')
                            ->relationship(
                                'learnerLevelClassifications',
                                'value',
                                fn ($query) => $query->whereHas('classificationType', function ($q) {
                                    $q->where('slug', 'learner-level');
                                })
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('CSV: Learner level'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Keywords')
                    ->schema([
                        Forms\Components\TagsInput::make('keyword_list')
                            ->label('Keywords')
                            ->placeholder('Add keywords')
                            ->helperText('CSV: Keywords - Press Enter after each keyword')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Book Files')
                    ->schema([
                        Forms\Components\Repeater::make('files')
                            ->relationship('files')
                            ->schema([
                                Forms\Components\Select::make('file_type')
                                    ->label('File Type')
                                    ->options([
                                        'pdf' => 'PDF Document',
                                        'thumbnail' => 'Thumbnail Image',
                                        'audio' => 'Audio File',
                                        'video' => 'Video (External URL)',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_primary')
                                    ->label('Primary')
                                    ->default(false)
                                    ->inline(false)
                                    ->helperText('Mark as primary file')
                                    ->columnSpan(1),

                                Forms\Components\FileUpload::make('file_path')
                                    ->label('File')
                                    ->disk('public')
                                    ->directory('books')
                                    ->visibility('public')
                                    ->preserveFilenames()
                                    ->maxSize(102400)
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'audio/*'])
                                    ->hidden(fn ($get) => $get('file_type') === 'video')
                                    ->helperText('Upload PDF, image, or audio file (max 100MB). Leave empty when editing to keep existing file.')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('external_url')
                                    ->label('External URL')
                                    ->url()
                                    ->placeholder('https://youtube.com/...')
                                    ->visible(fn ($get) => $get('file_type') === 'video')
                                    ->required(fn ($get) => $get('file_type') === 'video')
                                    ->helperText('CSV: Coupled video - YouTube/Vimeo link')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('digital_source')
                                    ->label('Digital Source')
                                    ->maxLength(255)
                                    ->placeholder('Where the file came from')
                                    ->helperText('CSV: DIGITAL SOURCE / ALTERNATIVE DIGITAL SOURCE')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('filename')
                                    ->label('Original Filename')
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add File')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Book Relationships')
                    ->schema([
                        Forms\Components\Repeater::make('bookRelationships')
                            ->relationship('bookRelationships')
                            ->schema([
                                Forms\Components\Select::make('relationship_type')
                                    ->label('Relationship Type')
                                    ->options([
                                        'same_version' => 'Same Version (Related same)',
                                        'same_language' => 'Same Language (Omnibus)',
                                        'supporting' => 'Supporting Material',
                                        'other_language' => 'Other Language Version',
                                    ])
                                    ->required()
                                    ->helperText('CSV: Related (same), Related (omnibus), Related (support), Related (same title, different language)')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('related_book_id')
                                    ->label('Related Book')
                                    ->relationship('relatedBook', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Select the related book')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('relationship_code')
                                    ->label('Relationship Code')
                                    ->maxLength(100)
                                    ->placeholder('Group code for related books')
                                    ->helperText('Used to group related books together')
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2)
                                    ->placeholder('Optional notes about this relationship')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Related Book')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Library References')
                    ->schema([
                        Forms\Components\Repeater::make('libraryReferences')
                            ->relationship('libraryReferences')
                            ->schema([
                                Forms\Components\Select::make('library_code')
                                    ->label('Library')
                                    ->options([
                                        'UH' => 'University of Hawaii',
                                        'COM' => 'COM Library',
                                    ])
                                    ->required()
                                    ->helperText('Select library system')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('library_name')
                                    ->label('Library Name')
                                    ->maxLength(255)
                                    ->placeholder('Full library name')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Reference Number')
                                    ->maxLength(100)
                                    ->placeholder('Library reference number')
                                    ->helperText('CSV: UH hard copy ref / COM hard copy ref')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('call_number')
                                    ->label('Call Number')
                                    ->maxLength(100)
                                    ->placeholder('Library call number')
                                    ->helperText('CSV: UH hard copy call number / COM hard copy call number')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('catalog_link')
                                    ->label('Catalog Link')
                                    ->url()
                                    ->maxLength(500)
                                    ->placeholder('https://...')
                                    ->helperText('CSV: UH hard copy link')
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->placeholder('Additional notes')
                                    ->helperText('CSV: UH note / COM hard copy ref NOTE')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Library Reference')
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
                    ->limit(50)
                    ->description(fn ($record) => $record->isDuplicate()
                        ? '📋 Duplicated from: ' . ($record->duplicatedFrom?->title ?? 'Unknown')
                        : ($record->hasBeenDuplicated()
                            ? '✨ Duplicated ' . $record->getDuplicateCount() . ' time(s)'
                            : null
                        )
                    ),

                Tables\Columns\TextColumn::make('duplication_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->isDuplicate() ? 'Duplicate' : null)
                    ->color('info')
                    ->icon('heroicon-o-document-duplicate')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(false),

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

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Book')
                    ->modalDescription(fn ($record) => "Create a copy of \"{$record->title}\" with all relationships and classifications.")
                    ->modalSubmitActionLabel('Duplicate')
                    ->action(function ($record) {
                        try {
                            // Validate before duplication
                            $validation = $record->canBeDuplicated();

                            if (!$validation['valid']) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Cannot Duplicate Book')
                                    ->body(implode("\n", $validation['errors']))
                                    ->persistent()
                                    ->send();
                                return;
                            }

                            // Perform duplication
                            $duplicate = $record->duplicate([
                                'clear_title' => false,
                                'append_copy_suffix' => true, // Add " (Copy)" to title
                            ]);

                            // Success notification with link to edit the duplicate
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Book Duplicated Successfully!')
                                ->body("Created duplicate of \"{$record->title}\". Click to edit the new book.")
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('edit')
                                        ->label('Edit New Book')
                                        ->url(static::getUrl('edit', ['record' => $duplicate->id]))
                                        ->button(),
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View List')
                                        ->url(static::getUrl('index'))
                                        ->button(),
                                ])
                                ->persistent()
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Duplication Failed')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    })
                    ->successNotification(null), // Disable default notification, we have custom one

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('duplicate')
                        ->label('Duplicate Selected')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Duplicate Multiple Books')
                        ->modalDescription(fn ($records) => "You are about to duplicate " . count($records) . " book(s). Each book will be copied with all its relationships and classifications.")
                        ->modalSubmitActionLabel('Duplicate All')
                        ->action(function ($records) {
                            $duplicationService = app(\App\Services\BookDuplicationService::class);
                            $bookIds = $records->pluck('id')->toArray();

                            try {
                                $results = $duplicationService->bulkDuplicate($bookIds, [
                                    'clear_title' => false,
                                    'append_copy_suffix' => true, // Add " (Copy)" to title
                                ]);

                                $successCount = count($results['success']);
                                $failedCount = count($results['failed']);

                                if ($successCount > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title("Duplicated {$successCount} Book(s)")
                                        ->body($failedCount > 0 ? "{$failedCount} book(s) failed to duplicate." : "All books duplicated successfully!")
                                        ->persistent()
                                        ->send();
                                }

                                if ($failedCount > 0) {
                                    $errorMessages = collect($results['failed'])
                                        ->map(fn($failure) => "Book ID {$failure['book_id']}: {$failure['error']}")
                                        ->join("\n");

                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('Some Duplications Failed')
                                        ->body($errorMessages)
                                        ->persistent()
                                        ->send();
                                }

                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Bulk Duplication Failed')
                                    ->body($e->getMessage())
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(null), // Custom notifications

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
