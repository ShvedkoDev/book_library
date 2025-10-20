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
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('subtitle')
                            ->maxLength(500)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('isbn')
                            ->label('ISBN')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                            
                        Forms\Components\TextInput::make('isbn13')
                            ->label('ISBN-13')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Publishing Details')
                    ->schema([
                        Forms\Components\Select::make('publisher_id')
                            ->label('Publisher')
                            ->relationship('publisher', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('collection_id')
                            ->label('Collection')
                            ->relationship('collection', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('publication_year')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(date('Y')),

                        Forms\Components\TextInput::make('pages')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Content & Files')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                            
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('book-covers'),
                            
                        Forms\Components\FileUpload::make('pdf_file')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('book-pdfs'),
                            
                        Forms\Components\TextInput::make('file_size')
                            ->numeric()
                            ->suffix('bytes')
                            ->disabled(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Select::make('access_level')
                            ->options([
                                'full' => 'Full Access',
                                'limited' => 'Limited Access',
                                'unavailable' => 'Unavailable'
                            ])
                            ->default('unavailable')
                            ->required(),
                            
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(4),
                    
                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('languages')
                            ->relationship('languages', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('creators')
                            ->relationship('creators', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('geographicLocations')
                            ->label('Geographic Locations')
                            ->relationship('geographicLocations', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
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
