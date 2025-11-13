<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use App\Models\ResourceContributor;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                Forms\Components\Section::make('Basic Information')
                    ->description('General page information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('slug') && $state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly version of the title'),
                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->nullable()
                            ->helperText('Select a parent page to create a hierarchical structure'),
                    ])
                    ->columns(2),

                // Content Section
                Forms\Components\Section::make('Content')
                    ->description('Page content and body')
                    ->schema([
                        TiptapEditor::make('content')
                            ->profile('default')
                            ->tools([
                                'heading',
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bullet-list',
                                'ordered-list',
                                'blockquote',
                                'code-block',
                                'link',
                                'media',
                                'table',
                                'grid-builder',
                                'align-left',
                                'align-center',
                                'align-right',
                                'color',
                                'highlight',
                                'hr',
                                'source', // Enable HTML source code editing
                            ])
                            ->disk('public')
                            ->directory('page-media')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull()
                            ->helperText('Use H2 headings for sections that will appear in the table of contents. Click the </> button to edit HTML source.'),
                    ]),

                // SEO & Metadata Section
                Forms\Components\Section::make('SEO & Metadata')
                    ->description('Search engine optimization')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Recommended: 150-160 characters. Leave empty to auto-generate from content.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('meta_keywords')
                            ->rows(2)
                            ->helperText('Comma-separated keywords for SEO')
                            ->columnSpanFull(),
                    ]),

                // Publishing Section
                Forms\Components\Section::make('Publishing')
                    ->description('Publication settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText('Toggle to publish or unpublish this page'),
                        Forms\Components\Toggle::make('show_in_navigation')
                            ->label('Show in Navigation')
                            ->default(true)
                            ->helperText('Toggle to show or hide this page in navigation menus'),
                        Forms\Components\Toggle::make('is_homepage')
                            ->label('Set as Homepage')
                            ->default(false)
                            ->helperText('This page will be shown at the root URL (/). Only one page can be homepage.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    Notification::make()
                                        ->title('Homepage Set')
                                        ->body('This page will now appear at the root URL (/). Any other homepage will be unset.')
                                        ->info()
                                        ->send();
                                }
                            }),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->nullable()
                            ->helperText('Leave empty to publish immediately'),
                        Forms\Components\TextInput::make('order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Order in navigation menus (lower numbers appear first)'),
                    ])
                    ->columns(5),

                // Resource Contributors Section
                Forms\Components\Section::make('Resource Contributors')
                    ->description('Organizations contributing to this page')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\CheckboxList::make('resourceContributors')
                            ->relationship('resourceContributors', 'name')
                            ->options(ResourceContributor::active()->ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('Select contributors associated with this page'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn (Page $record): string => $record->excerpt ? Str::limit($record->excerpt, 80) : ''),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Slug copied!')
                    ->icon('heroicon-m-link'),
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Published')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish Date')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Parent Page')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('(Root)'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publication Status')
                    ->placeholder('All pages')
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts only'),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Page')
                    ->relationship('parent', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Page $record): ?string => $record->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Page $record): bool => $record->isPublished() && $record->getUrl() !== null),
                Tables\Actions\Action::make('previewSections')
                    ->label('Preview Sections')
                    ->icon('heroicon-m-list-bullet')
                    ->modalHeading('Page Sections (H2 Anchors)')
                    ->modalDescription(fn (Page $record) => "Sections found in: {$record->title}")
                    ->modalContent(function (Page $record) {
                        $sections = $record->extractSections();

                        if (empty($sections)) {
                            return view('filament.resources.page-resource.no-sections');
                        }

                        return view('filament.resources.page-resource.sections-preview', [
                            'sections' => $sections,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\ReplicateAction::make('duplicate')
                    ->label('Duplicate')
                    ->requiresConfirmation()
                    ->excludeAttributes(['slug'])
                    ->beforeReplicaSaved(function (Page $replica): void {
                        $replica->slug = $replica->slug . '-copy';
                        $replica->is_published = false;
                        $replica->published_at = null;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Page duplicated')
                            ->body('The page has been duplicated successfully.'),
                    ),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
