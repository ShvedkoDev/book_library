<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentBlockResource\Pages;
use App\Models\ContentBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Content Blocks';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('block_type')
                    ->label('Block Type')
                    ->options([
                        'text' => 'Text Content',
                        'image' => 'Single Image',
                        'gallery' => 'Image Gallery',
                        'video' => 'Video Embed',
                        'quote' => 'Quote/Testimonial',
                        'code' => 'Code Block',
                        'cta' => 'Call to Action',
                        'divider' => 'Section Divider',
                        'table' => 'Data Table',
                        'accordion' => 'Accordion/FAQ',
                        'list' => 'Bullet/Numbered List',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('content', [])),

                Forms\Components\Group::make()
                    ->schema(fn (Forms\Get $get): array => match ($get('block_type')) {
                        'text' => [
                            Forms\Components\TextInput::make('content.title')
                                ->label('Title (Optional)')
                                ->maxLength(255),
                            Forms\Components\RichEditor::make('content.content')
                                ->label('Content')
                                ->required()
                                ->toolbarButtons([
                                    'attachFiles',
                                    'blockquote',
                                    'bold',
                                    'bulletList',
                                    'codeBlock',
                                    'h2',
                                    'h3',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ]),
                            Forms\Components\Select::make('content.heading_level')
                                ->label('Title Heading Level')
                                ->options([
                                    'h1' => 'H1',
                                    'h2' => 'H2',
                                    'h3' => 'H3',
                                    'h4' => 'H4',
                                    'h5' => 'H5',
                                    'h6' => 'H6',
                                ])
                                ->default('h2')
                                ->visible(fn (Forms\Get $get) => !empty($get('content.title')))
                        ],

                        'image' => [
                            Forms\Components\FileUpload::make('content.image')
                                ->label('Image')
                                ->image()
                                ->required(),
                            Forms\Components\TextInput::make('content.alt')
                                ->label('Alt Text')
                                ->required(),
                            Forms\Components\TextInput::make('content.caption')
                                ->label('Caption')
                        ],

                        'gallery' => [
                            Forms\Components\FileUpload::make('content.images')
                                ->label('Images')
                                ->image()
                                ->multiple()
                                ->required(),
                            Forms\Components\Textarea::make('content.description')
                                ->label('Gallery Description')
                        ],

                        'video' => [
                            Forms\Components\TextInput::make('content.url')
                                ->label('Video URL')
                                ->url()
                                ->required()
                                ->helperText('YouTube, Vimeo, or direct video URL'),
                            Forms\Components\TextInput::make('content.title')
                                ->label('Video Title'),
                            Forms\Components\Textarea::make('content.description')
                                ->label('Description')
                        ],

                        'quote' => [
                            Forms\Components\Textarea::make('content.quote')
                                ->label('Quote Text')
                                ->required(),
                            Forms\Components\TextInput::make('content.author')
                                ->label('Author'),
                            Forms\Components\TextInput::make('content.position')
                                ->label('Author Position/Title'),
                            Forms\Components\FileUpload::make('content.author_image')
                                ->label('Author Image')
                                ->image()
                        ],

                        'code' => [
                            Forms\Components\Select::make('content.language')
                                ->label('Programming Language')
                                ->options([
                                    'php' => 'PHP',
                                    'javascript' => 'JavaScript',
                                    'python' => 'Python',
                                    'html' => 'HTML',
                                    'css' => 'CSS',
                                    'sql' => 'SQL',
                                    'bash' => 'Bash',
                                    'json' => 'JSON',
                                    'xml' => 'XML',
                                    'yaml' => 'YAML',
                                ])
                                ->default('php'),
                            Forms\Components\Textarea::make('content.code')
                                ->label('Code')
                                ->required()
                                ->rows(10)
                        ],

                        'cta' => [
                            Forms\Components\TextInput::make('content.heading')
                                ->label('Heading')
                                ->required(),
                            Forms\Components\Textarea::make('content.description')
                                ->label('Description'),
                            Forms\Components\TextInput::make('content.button_text')
                                ->label('Button Text')
                                ->required(),
                            Forms\Components\TextInput::make('content.button_url')
                                ->label('Button URL')
                                ->url()
                                ->required(),
                            Forms\Components\Toggle::make('content.button_new_tab')
                                ->label('Open in New Tab')
                                ->default(false)
                        ],

                        'divider' => [
                            Forms\Components\Select::make('content.style')
                                ->label('Divider Style')
                                ->options([
                                    'line' => 'Simple Line',
                                    'dots' => 'Dots',
                                    'wave' => 'Wave',
                                    'zigzag' => 'Zigzag',
                                ])
                                ->default('line'),
                            Forms\Components\TextInput::make('content.text')
                                ->label('Divider Text (Optional)')
                        ],

                        'table' => [
                            Forms\Components\TextInput::make('content.caption')
                                ->label('Table Caption'),
                            Forms\Components\Repeater::make('content.headers')
                                ->label('Table Headers')
                                ->schema([
                                    Forms\Components\TextInput::make('text')
                                        ->required()
                                ])
                                ->minItems(1)
                                ->addActionLabel('Add Header'),
                            Forms\Components\Repeater::make('content.rows')
                                ->label('Table Rows')
                                ->schema([
                                    Forms\Components\Repeater::make('cells')
                                        ->label('Row Cells')
                                        ->schema([
                                            Forms\Components\TextInput::make('text')
                                                ->required()
                                        ])
                                        ->minItems(1)
                                        ->addActionLabel('Add Cell')
                                ])
                                ->minItems(1)
                                ->addActionLabel('Add Row')
                        ],

                        'accordion' => [
                            Forms\Components\Repeater::make('content.items')
                                ->label('Accordion Items')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required(),
                                    Forms\Components\RichEditor::make('content')
                                        ->required()
                                ])
                                ->minItems(1)
                                ->addActionLabel('Add Item')
                        ],

                        'list' => [
                            Forms\Components\Select::make('content.type')
                                ->label('List Type')
                                ->options([
                                    'bulleted' => 'Bulleted List',
                                    'numbered' => 'Numbered List',
                                    'checklist' => 'Checklist',
                                ])
                                ->required()
                                ->default('bulleted'),
                            Forms\Components\Repeater::make('content.items')
                                ->label('List Items')
                                ->schema([
                                    Forms\Components\TextInput::make('text')
                                        ->required(),
                                    Forms\Components\Toggle::make('checked')
                                        ->label('Checked (for checklists)')
                                        ->visible(fn (Forms\Get $get) => $get('../../content.type') === 'checklist')
                                ])
                                ->minItems(1)
                                ->addActionLabel('Add Item')
                        ],

                        default => []
                    }),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('settings.css_class')
                            ->label('CSS Class'),
                        Forms\Components\TextInput::make('settings.id')
                            ->label('HTML ID'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->collapsed(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('block_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => 'Text',
                        'image' => 'Image',
                        'gallery' => 'Gallery',
                        'video' => 'Video',
                        'quote' => 'Quote',
                        'code' => 'Code',
                        'cta' => 'CTA',
                        'divider' => 'Divider',
                        'table' => 'Table',
                        'accordion' => 'Accordion',
                        'list' => 'List',
                        default => ucfirst($state)
                    })
                    ->colors([
                        'primary' => 'text',
                        'success' => 'image',
                        'warning' => 'video',
                        'danger' => 'code',
                        'secondary' => fn ($state) => in_array($state, ['gallery', 'quote', 'cta', 'divider', 'table', 'accordion', 'list'])
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('block_type')
                    ->options([
                        'text' => 'Text Content',
                        'image' => 'Single Image',
                        'gallery' => 'Image Gallery',
                        'video' => 'Video Embed',
                        'quote' => 'Quote/Testimonial',
                        'code' => 'Code Block',
                        'cta' => 'Call to Action',
                        'divider' => 'Section Divider',
                        'table' => 'Data Table',
                        'accordion' => 'Accordion/FAQ',
                        'list' => 'Bullet/Numbered List',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListContentBlocks::route('/'),
            'create' => Pages\CreateContentBlock::route('/create'),
            'edit' => Pages\EditContentBlock::route('/{record}/edit'),
        ];
    }
}