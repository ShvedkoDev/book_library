<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource\RelationManagers;
use App\Services\Cms\MediaService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'Media File';

    protected static ?string $pluralModelLabel = 'Media Files';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Media Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('File Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('collection_name')
                                    ->label('Collection')
                                    ->options([
                                        'page_featured' => 'Page Featured Images',
                                        'page_gallery' => 'Page Gallery',
                                        'content_blocks' => 'Content Block Media',
                                        'documents' => 'Documents',
                                        'videos' => 'Video Files',
                                        'seo_images' => 'SEO Images',
                                    ])
                                    ->required(),

                                Forms\Components\FileUpload::make('file')
                                    ->label('Upload New File')
                                    ->disk('public')
                                    ->directory('cms/media')
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/gif',
                                        'image/webp',
                                        'image/svg+xml',
                                        'application/pdf',
                                        'video/mp4',
                                        'video/webm',
                                    ])
                                    ->maxSize(51200) // 50MB
                                    ->imageResizeMode('force')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1080')
                                    ->imageOptimization()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $file = $state;
                                            $set('mime_type', $file->getMimeType());
                                            $set('size', $file->getSize());
                                        }
                                    }),

                                Forms\Components\TextInput::make('file_name')
                                    ->label('Original File Name')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('mime_type')
                                    ->label('MIME Type')
                                    ->disabled(),

                                Forms\Components\TextInput::make('size')
                                    ->label('File Size')
                                    ->disabled()
                                    ->formatStateUsing(fn (?string $state): string =>
                                        $state ? self::formatBytes((int) $state) : ''
                                    ),

                                Forms\Components\TextInput::make('disk')
                                    ->label('Storage Disk')
                                    ->disabled()
                                    ->default('public'),
                            ]),
                    ]),

                Forms\Components\Section::make('Metadata & SEO')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('custom_properties.alt')
                                    ->label('Alt Text')
                                    ->helperText('Important for accessibility and SEO')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('custom_properties.title')
                                    ->label('Title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('custom_properties.description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('custom_properties.caption')
                                    ->label('Caption')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('custom_properties.credit')
                                    ->label('Credit/Attribution')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TagsInput::make('custom_properties.keywords')
                                    ->label('Keywords/Tags')
                                    ->separator(','),

                                Forms\Components\Select::make('custom_properties.folder')
                                    ->label('Folder')
                                    ->options([
                                        'general' => 'General',
                                        'featured' => 'Featured',
                                        'gallery' => 'Gallery',
                                        'documents' => 'Documents',
                                        'seo' => 'SEO',
                                        'content-blocks' => 'Content Blocks',
                                    ])
                                    ->default('general'),

                                Forms\Components\Toggle::make('custom_properties.featured')
                                    ->label('Featured Media')
                                    ->default(false),
                            ]),
                    ]),

                Forms\Components\Section::make('Usage & Analytics')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('custom_properties.download_count')
                                    ->label('Download Count')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),

                                Forms\Components\TextInput::make('custom_properties.view_count')
                                    ->label('View Count')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),

                                Forms\Components\DatePicker::make('custom_properties.expires_at')
                                    ->label('Expiration Date')
                                    ->helperText('Leave empty for no expiration'),

                                Forms\Components\Toggle::make('custom_properties.private')
                                    ->label('Private File')
                                    ->helperText('Requires authentication to access')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(function (Media $record): ?string {
                        if (str_starts_with($record->mime_type, 'image/')) {
                            return $record->getUrl('thumbnail');
                        }
                        return null;
                    })
                    ->defaultImageUrl('/images/file-icon.png')
                    ->size(60)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('collection_name')
                    ->label('Collection')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'page_featured' => 'Featured',
                        'page_gallery' => 'Gallery',
                        'content_blocks' => 'Content',
                        'documents' => 'Documents',
                        'videos' => 'Videos',
                        'seo_images' => 'SEO',
                        default => ucfirst($state)
                    })
                    ->color(fn (string $state): string => match($state) {
                        'page_featured' => 'success',
                        'page_gallery' => 'info',
                        'content_blocks' => 'warning',
                        'documents' => 'danger',
                        'videos' => 'primary',
                        'seo_images' => 'gray',
                        default => 'secondary'
                    }),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string =>
                        str_replace(['image/', 'application/', 'video/'], '', $state)
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn (?int $state): string =>
                        $state ? self::formatBytes($state) : ''
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('custom_properties.alt')
                    ->label('Alt Text')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('custom_properties.featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('model_type')
                    ->label('Used By')
                    ->formatStateUsing(fn (?string $state): string =>
                        $state ? class_basename($state) : 'Unused'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_name')
                    ->label('Collection')
                    ->options([
                        'page_featured' => 'Page Featured',
                        'page_gallery' => 'Page Gallery',
                        'content_blocks' => 'Content Blocks',
                        'documents' => 'Documents',
                        'videos' => 'Videos',
                        'seo_images' => 'SEO Images',
                    ]),

                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('File Type')
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/gif' => 'GIF',
                        'image/webp' => 'WebP',
                        'image/svg+xml' => 'SVG',
                        'application/pdf' => 'PDF',
                        'video/mp4' => 'MP4',
                        'video/webm' => 'WebM',
                    ]),

                Tables\Filters\Filter::make('featured')
                    ->label('Featured Only')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereJsonContains('custom_properties->featured', true)
                    ),

                Tables\Filters\Filter::make('unused')
                    ->label('Unused Files')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNull('model_type')
                    ),

                Tables\Filters\Filter::make('large_files')
                    ->label('Large Files (>5MB)')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('size', '>', 5 * 1024 * 1024)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Media $record) {
                        // Increment download count
                        $downloads = $record->getCustomProperty('download_count', 0);
                        $record->setCustomProperty('download_count', $downloads + 1);
                        $record->save();

                        return response()->download($record->getPath(), $record->file_name);
                    }),

                Tables\Actions\Action::make('optimize')
                    ->label('Optimize')
                    ->icon('heroicon-o-sparkles')
                    ->visible(fn (Media $record): bool => str_starts_with($record->mime_type, 'image/'))
                    ->action(function (Media $record) {
                        $mediaService = app(MediaService::class);
                        $mediaService->optimizeMedia($record);

                        Notification::make()
                            ->title('Media optimized successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('regenerate_conversions')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Media $record): bool => str_starts_with($record->mime_type, 'image/'))
                    ->action(function (Media $record) {
                        $record->clearMediaConversions();

                        // Trigger conversion regeneration
                        if ($record->model) {
                            $record->model->registerMediaConversions($record);
                        }

                        Notification::make()
                            ->title('Conversions regenerated successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Media $record) {
                        // Check if media is in use
                        $mediaService = app(MediaService::class);
                        if ($mediaService->isMediaInUse($record)) {
                            Notification::make()
                                ->title('Cannot delete media')
                                ->body('This media file is currently in use and cannot be deleted.')
                                ->danger()
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $mediaService = app(MediaService::class);
                            $inUse = [];

                            foreach ($records as $record) {
                                if ($mediaService->isMediaInUse($record)) {
                                    $inUse[] = $record->name;
                                }
                            }

                            if (!empty($inUse)) {
                                Notification::make()
                                    ->title('Some files cannot be deleted')
                                    ->body('The following files are in use: ' . implode(', ', $inUse))
                                    ->warning()
                                    ->send();
                            }
                        }),

                    Tables\Actions\BulkAction::make('optimize')
                        ->label('Optimize Selected')
                        ->icon('heroicon-o-sparkles')
                        ->action(function ($records) {
                            $mediaService = app(MediaService::class);
                            $optimized = 0;

                            foreach ($records as $record) {
                                if (str_starts_with($record->mime_type, 'image/')) {
                                    $mediaService->optimizeMedia($record);
                                    $optimized++;
                                }
                            }

                            Notification::make()
                                ->title("Optimized {$optimized} images")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('move_to_collection')
                        ->label('Move to Collection')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\Select::make('collection')
                                ->label('Target Collection')
                                ->options([
                                    'page_featured' => 'Page Featured',
                                    'page_gallery' => 'Page Gallery',
                                    'content_blocks' => 'Content Blocks',
                                    'documents' => 'Documents',
                                    'videos' => 'Videos',
                                    'seo_images' => 'SEO Images',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['collection_name' => $data['collection']]);
                            }

                            Notification::make()
                                ->title('Files moved successfully')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Media Preview')
                    ->schema([
                        Infolists\Components\ImageEntry::make('url')
                            ->label('Preview')
                            ->getStateUsing(function (Media $record): ?string {
                                if (str_starts_with($record->mime_type, 'image/')) {
                                    return $record->getUrl();
                                }
                                return null;
                            })
                            ->visible(fn (Media $record): bool => str_starts_with($record->mime_type, 'image/'))
                            ->size(300),

                        Infolists\Components\TextEntry::make('file_name')
                            ->label('File Name')
                            ->visible(fn (Media $record): bool => !str_starts_with($record->mime_type, 'image/')),
                    ]),

                Infolists\Components\Section::make('File Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Name'),

                                Infolists\Components\TextEntry::make('collection_name')
                                    ->label('Collection'),

                                Infolists\Components\TextEntry::make('mime_type')
                                    ->label('MIME Type'),

                                Infolists\Components\TextEntry::make('size')
                                    ->label('File Size')
                                    ->formatStateUsing(fn (?int $state): string =>
                                        $state ? self::formatBytes($state) : ''
                                    ),

                                Infolists\Components\TextEntry::make('disk')
                                    ->label('Storage Disk'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Uploaded At')
                                    ->dateTime(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('custom_properties.alt')
                            ->label('Alt Text'),

                        Infolists\Components\TextEntry::make('custom_properties.title')
                            ->label('Title'),

                        Infolists\Components\TextEntry::make('custom_properties.description')
                            ->label('Description'),

                        Infolists\Components\TextEntry::make('custom_properties.keywords')
                            ->label('Keywords')
                            ->formatStateUsing(fn ($state): string =>
                                is_array($state) ? implode(', ', $state) : (string) $state
                            ),
                    ]),

                Infolists\Components\Section::make('Usage Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('custom_properties.view_count')
                                    ->label('Views')
                                    ->default('0'),

                                Infolists\Components\TextEntry::make('custom_properties.download_count')
                                    ->label('Downloads')
                                    ->default('0'),

                                Infolists\Components\TextEntry::make('model_type')
                                    ->label('Used By')
                                    ->formatStateUsing(fn (?string $state): string =>
                                        $state ? class_basename($state) : 'Not used'
                                    ),
                            ]),
                    ]),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['model']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'file_name', 'custom_properties.alt', 'custom_properties.title'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Collection' => $record->collection_name,
            'Type' => $record->mime_type,
            'Size' => self::formatBytes($record->size),
        ];
    }
}