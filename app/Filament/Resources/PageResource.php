<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = "heroicon-o-document-text";

    protected static ?string $navigationGroup = "CMS";

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = "title";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make("Page Details")
                    ->tabs([
                        Forms\Components\Tabs\Tab::make("Basic Information")
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make("title")
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                                if ($context === "edit") {
                                                    return;
                                                }
                                                $set("slug", str()->slug($state));
                                            }),

                                        Forms\Components\TextInput::make("slug")
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Page::class, "slug", ignoreRecord: true)
                                            ->rules(["regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/"])
                                            ->helperText("URL-friendly version of the title"),
                                    ]),

                                Forms\Components\Textarea::make("excerpt")
                                    ->maxLength(500)
                                    ->helperText("Brief description for search results and previews")
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make("status")
                                            ->options([
                                                "draft" => "Draft",
                                                "published" => "Published",
                                                "scheduled" => "Scheduled",
                                                "archived" => "Archived",
                                            ])
                                            ->default("draft")
                                            ->required(),

                                        Forms\Components\Select::make("template")
                                            ->options(function () {
                                                return collect(config("cms.templates", []))
                                                    ->mapWithKeys(fn ($template, $key) => [$key => $template["name"]]);
                                            })
                                            ->default("default")
                                            ->required(),

                                        Forms\Components\DateTimePicker::make("published_at")
                                            ->label("Publish Date")
                                            ->helperText("Leave empty to publish immediately")
                                            ->visible(fn (Forms\Get $get) => in_array($get("status"), ["published", "scheduled"])),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make("Content")
                            ->schema([
                                Forms\Components\RichEditor::make("content")
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        "attachFiles",
                                        "blockquote",
                                        "bold",
                                        "bulletList",
                                        "codeBlock",
                                        "h2",
                                        "h3",
                                        "italic",
                                        "link",
                                        "orderedList",
                                        "redo",
                                        "strike",
                                        "underline",
                                        "undo",
                                    ]),

                                Forms\Components\Placeholder::make("word_count")
                                    ->content(function (Forms\Get $get) {
                                        $content = $get("content");
                                        if (!$content) return "0 words";
                                        return str_word_count(strip_tags($content)) . " words";
                                    })
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make("Featured Image")
                            ->schema([
                                Forms\Components\FileUpload::make("featured_image")
                                    
                                    ->image()
                                    
                                    
                                    
                                    
                                    ->maxSize(5120)
                                    ->helperText("Recommended size: 1200x675 pixels"),

                                Forms\Components\TextInput::make("featured_image_alt")
                                    ->label("Alt Text")
                                    ->maxLength(255)
                                    ->helperText("Describe the image for accessibility"),
                            ]),

                        Forms\Components\Tabs\Tab::make("Content Blocks")
                            ->schema([
                                Forms\Components\Repeater::make("content_blocks")
                                    ->relationship("contentBlocks")
                                    ->schema([
                                        Forms\Components\Select::make("type")
                                            ->options([
                                                "text" => "Text Block",
                                                "image" => "Image",
                                                "gallery" => "Image Gallery",
                                                "video" => "Video",
                                                "quote" => "Quote",
                                                "code" => "Code Block",
                                                "cta" => "Call to Action",
                                                "divider" => "Divider",
                                                "table" => "Table",
                                                "accordion" => "Accordion",
                                                "embed" => "Embed",
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set("data", [])),

                                        Forms\Components\TextInput::make("title")
                                            ->maxLength(255),

                                        Forms\Components\Group::make([
                                            Forms\Components\RichEditor::make("data.content")
                                                ->label("Content")
                                                ->required(fn (Forms\Get $get) => $get("type") === "text")
                                                ->visible(fn (Forms\Get $get) => $get("type") === "text")
                                                ->placeholder("Enter your content here...")
                                                ->validationMessages([
                                                    'required' => 'Please enter content for this text block.',
                                                ]),

                                            Forms\Components\FileUpload::make("data.image")
                                                ->label("Image")
                                                ->image()
                                                ->required(fn (Forms\Get $get) => $get("type") === "image")
                                                ->visible(fn (Forms\Get $get) => $get("type") === "image"),

                                            Forms\Components\FileUpload::make("data.images")
                                                ->label("Images")
                                                ->image()
                                                ->multiple()
                                                ->reorderable()
                                                ->required(fn (Forms\Get $get) => $get("type") === "gallery")
                                                ->visible(fn (Forms\Get $get) => $get("type") === "gallery"),

                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\TextInput::make("data.video_url")
                                                        ->label("Video URL")
                                                        ->url()
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "video"),
                                                    Forms\Components\Select::make("data.video_type")
                                                        ->label("Video Type")
                                                        ->options([
                                                            "youtube" => "YouTube",
                                                            "vimeo" => "Vimeo",
                                                            "direct" => "Direct Link",
                                                        ])
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "video"),
                                                ])
                                                ->visible(fn (Forms\Get $get) => $get("type") === "video"),

                                            Forms\Components\Grid::make(1)
                                                ->schema([
                                                    Forms\Components\Textarea::make("data.quote_text")
                                                        ->label("Quote Text")
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "quote")
                                                        ->rows(3),
                                                    Forms\Components\TextInput::make("data.quote_author")
                                                        ->label("Author")
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make("data.quote_source")
                                                        ->label("Source")
                                                        ->maxLength(255),
                                                ])
                                                ->visible(fn (Forms\Get $get) => $get("type") === "quote"),

                                            Forms\Components\Grid::make(1)
                                                ->schema([
                                                    Forms\Components\Select::make("data.language")
                                                        ->label("Programming Language")
                                                        ->options([
                                                            "html" => "HTML",
                                                            "css" => "CSS",
                                                            "javascript" => "JavaScript",
                                                            "php" => "PHP",
                                                            "python" => "Python",
                                                            "sql" => "SQL",
                                                            "bash" => "Bash",
                                                        ])
                                                        ->default("html"),
                                                    Forms\Components\Textarea::make("data.code")
                                                        ->label("Code")
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "code")
                                                        ->rows(10)
                                                        ->extraAttributes(["style" => "font-family: monospace;"]),
                                                ])
                                                ->visible(fn (Forms\Get $get) => $get("type") === "code"),

                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\TextInput::make("data.cta_text")
                                                        ->label("Button Text")
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "cta")
                                                        ->maxLength(100),
                                                    Forms\Components\TextInput::make("data.cta_url")
                                                        ->label("Button URL")
                                                        ->required(fn (Forms\Get $get) => $get("../../type") === "cta")
                                                        ->url(),
                                                    Forms\Components\Select::make("data.cta_style")
                                                        ->label("Button Style")
                                                        ->options([
                                                            "primary" => "Primary",
                                                            "secondary" => "Secondary",
                                                            "outline" => "Outline",
                                                        ])
                                                        ->default("primary"),
                                                    Forms\Components\Toggle::make("data.cta_new_tab")
                                                        ->label("Open in New Tab")
                                                        ->default(false),
                                                ])
                                                ->visible(fn (Forms\Get $get) => $get("type") === "cta"),

                                            Forms\Components\Select::make("data.divider_style")
                                                ->label("Divider Style")
                                                ->options([
                                                    "line" => "Simple Line",
                                                    "dashed" => "Dashed Line",
                                                    "decorative" => "Decorative",
                                                    "spacer" => "Spacer Only",
                                                ])
                                                ->default("line")
                                                ->visible(fn (Forms\Get $get) => $get("type") === "divider"),

                                            Forms\Components\Textarea::make("data.table_data")
                                                ->label("Table Data (CSV format)")
                                                ->placeholder("Header1,Header2,Header3\\nRow1Col1,Row1Col2,Row1Col3\\nRow2Col1,Row2Col2,Row2Col3")
                                                ->required(fn (Forms\Get $get) => $get("type") === "table")
                                                ->rows(6)
                                                ->visible(fn (Forms\Get $get) => $get("type") === "table"),

                                            Forms\Components\Repeater::make("data.accordion_items")
                                                ->label("Accordion Items")
                                                ->schema([
                                                    Forms\Components\TextInput::make("title")
                                                        ->required()
                                                        ->maxLength(255),
                                                    Forms\Components\RichEditor::make("content")
                                                        ->required()
                                                        ->columnSpanFull(),
                                                ])
                                                ->defaultItems(1)
                                                ->visible(fn (Forms\Get $get) => $get("type") === "accordion"),

                                            Forms\Components\Textarea::make("data.embed_code")
                                                ->label("Embed Code")
                                                ->placeholder("<iframe src=\"...\" ...></iframe>")
                                                ->required(fn (Forms\Get $get) => $get("type") === "embed")
                                                ->rows(4)
                                                ->visible(fn (Forms\Get $get) => $get("type") === "embed"),
                                        ]),

                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\TextInput::make("sort_order")
                                                    ->label("Order")
                                                    ->numeric()
                                                    ->default(0),
                                                Forms\Components\Toggle::make("is_visible")
                                                    ->label("Visible")
                                                    ->default(true),
                                                Forms\Components\Select::make("css_class")
                                                    ->label("CSS Class")
                                                    ->options([
                                                        "container" => "Container",
                                                        "full-width" => "Full Width",
                                                        "text-center" => "Centered",
                                                        "highlight" => "Highlighted",
                                                    ])
                                                    ->multiple(),
                                                Forms\Components\KeyValue::make("custom_attributes")
                                                    ->label("Custom Attributes")
                                                    ->keyLabel("Attribute")
                                                    ->valueLabel("Value"),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state["title"] ?? $state["type"] ?? null)
                                    ->reorderable("sort_order")
                                    ->defaultItems(0)
                                    ->addActionLabel("Add Content Block")
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make("SEO")
                            ->schema([
                                Forms\Components\Grid::make(1)
                                    ->schema([
                                        Forms\Components\TextInput::make("meta_title")
                                            ->label("Meta Title")
                                            ->maxLength(60)
                                            ->helperText("Recommended: 50-60 characters")
                                            ->hint(fn ($state) => (60 - strlen($state ?? "")) . " characters remaining"),

                                        Forms\Components\Textarea::make("meta_description")
                                            ->label("Meta Description")
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText("Recommended: 150-160 characters")
                                            ->hint(fn ($state) => (160 - strlen($state ?? "")) . " characters remaining"),

                                        Forms\Components\TagsInput::make("meta_keywords")
                                            ->label("Meta Keywords")
                                            ->helperText("Separate keywords with commas"),

                                        Forms\Components\TextInput::make("canonical_url")
                                            ->label("Canonical URL")
                                            ->url()
                                            ->helperText("Leave empty to use the page URL"),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make("robots_index")
                                                    ->label("Allow Search Indexing")
                                                    ->default(true)
                                                    ->helperText("Should search engines index this page?"),

                                                Forms\Components\Toggle::make("robots_follow")
                                                    ->label("Allow Link Following")
                                                    ->default(true)
                                                    ->helperText("Should search engines follow links on this page?"),
                                            ]),

                                        Forms\Components\Placeholder::make("seo_score")
                                            ->label("SEO Score")
                                            ->content(function (Forms\Get $get) {
                                                $score = 0;
                                                $total = 5;

                                                if (strlen($get("meta_title") ?? "") >= 30) $score++;
                                                if (strlen($get("meta_description") ?? "") >= 120) $score++;
                                                if (!empty($get("meta_keywords"))) $score++;
                                                if (!empty($get("excerpt"))) $score++;
                                                if (!empty($get("featured_image"))) $score++;

                                                $percentage = round(($score / $total) * 100);
                                                $color = $percentage >= 80 ? "success" : ($percentage >= 60 ? "warning" : "danger");

                                                return "<div class=\"text-{$color}-600 font-medium\">{$percentage}% ({$score}/{$total})</div>";
                                            })
                                            ->extraAttributes(["class" => "text-sm"]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make("Publishing")
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make("is_featured")
                                            ->label("Featured Page")
                                            ->helperText("Show this page in featured sections"),

                                        Forms\Components\Toggle::make("allow_comments")
                                            ->label("Allow Comments")
                                            ->default(true)
                                            ->helperText("Enable commenting for this page"),

                                        Forms\Components\Toggle::make("is_sticky")
                                            ->label("Sticky Post")
                                            ->helperText("Pin this page to the top of listings"),

                                        Forms\Components\Toggle::make("require_auth")
                                            ->label("Require Authentication")
                                            ->helperText("Only logged-in users can view this page"),
                                    ]),

                                Forms\Components\Select::make("visibility")
                                    ->label("Page Visibility")
                                    ->options([
                                        "public" => "Public - Visible to everyone",
                                        "private" => "Private - Only visible to administrators",
                                        "password" => "Password Protected",
                                        "members" => "Members Only",
                                    ])
                                    ->default("public")
                                    ->reactive(),

                                Forms\Components\TextInput::make("password")
                                    ->label("Page Password")
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get("visibility") === "password"),

                                Forms\Components\DateTimePicker::make("expires_at")
                                    ->label("Expiration Date")
                                    ->helperText("Page will be automatically unpublished after this date"),

                                Forms\Components\Textarea::make("author_notes")
                                    ->label("Author Notes")
                                    ->helperText("Internal notes for content editors")
                                    ->rows(3),
                            ]),

                        Forms\Components\Tabs\Tab::make("Categories")
                            ->schema([
                                Forms\Components\CheckboxList::make("categories")
                                    ->relationship("categories", "name")
                                    ->options(
                                        \App\Models\CmsCategory::query()
                                            ->where("is_active", true)
                                            ->orderBy("name")
                                            ->pluck("name", "id")
                                    )
                                    ->columns(2)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->helperText("Select one or more categories for this page"),


                                Forms\Components\TagsInput::make("tags")
                                    ->label("Tags")
                                    ->helperText("Add custom tags for better organization")
                                    ->suggestions([
                                        "popular",
                                        "trending",
                                        "tutorial",
                                        "guide",
                                        "news",
                                        "announcement",
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make("featured_image")
                    ->label("Image")
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl("/images/placeholder.png"),

                Tables\Columns\TextColumn::make("title")
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make("slug")
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make("status")
                    ->colors([
                        "secondary" => "draft",
                        "success" => "published",
                        "warning" => "scheduled",
                        "danger" => "archived",
                    ])
                    ->icons([
                        "heroicon-o-pencil" => "draft",
                        "heroicon-o-eye" => "published",
                        "heroicon-o-clock" => "scheduled",
                        "heroicon-o-archive-box" => "archived",
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make("template")
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("categories.name")
                    ->label("Categories")
                    ->badge()
                    ->color("gray")
                    ->limit(2)
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                Tables\Columns\IconColumn::make("is_featured")
                    ->label("Featured")
                    ->boolean()
                    ->trueIcon("heroicon-o-star")
                    ->falseIcon("heroicon-o-star")
                    ->trueColor("warning")
                    ->falseColor("gray")
                    ->sortable(),

                Tables\Columns\TextColumn::make("views_count")
                    ->label("Views")
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("word_count")
                    ->label("Words")
                    ->getStateUsing(function ($record) {
                        return str_word_count(strip_tags($record->content));
                    })
                    ->numeric()
                    ->sortable(false)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("published_at")
                    ->label("Published")
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("creator.name")
                    ->label("Author")
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label("Created")
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("updated_at")
                    ->label("Updated")
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("status")
                    ->options([
                        "draft" => "Draft",
                        "published" => "Published",
                        "scheduled" => "Scheduled",
                        "archived" => "Archived",
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make("template")
                    ->options(function () {
                        return collect(config("cms.templates", []))
                            ->mapWithKeys(fn ($template, $key) => [$key => $template["name"]]);
                    })
                    ->multiple(),

                Tables\Filters\Filter::make("is_featured")
                    ->query(fn (Builder $query): Builder => $query->where("is_featured", true))
                    ->label("Featured Only")
                    ->toggle(),

                Tables\Filters\Filter::make("has_featured_image")
                    ->query(fn (Builder $query): Builder => $query->whereHas("media", function ($query) {
                        $query->where("collection_name", "featured_images");
                    }))
                    ->label("Has Featured Image")
                    ->toggle(),

                Tables\Filters\SelectFilter::make("categories")
                    ->relationship("categories", "name")
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make("creator")
                    ->relationship("creator", "name")
                    ->label("Author")
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make("published_date")
                    ->form([
                        Forms\Components\DatePicker::make("published_from")
                            ->label("Published From"),
                        Forms\Components\DatePicker::make("published_until")
                            ->label("Published Until"),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data["published_from"],
                                fn (Builder $query, $date): Builder => $query->whereDate("published_at", ">=", $date),
                            )
                            ->when(
                                $data["published_until"],
                                fn (Builder $query, $date): Builder => $query->whereDate("published_at", "<=", $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([

                Tables\Actions\Action::make("quick_edit")
                    ->label("Quick Edit")
                    ->icon("heroicon-o-pencil-square")
                    ->color("warning")
                    ->form([
                        Forms\Components\TextInput::make("title")
                            ->required(),
                        Forms\Components\Select::make("status")
                            ->options([
                                "draft" => "Draft",
                                "published" => "Published",
                                "scheduled" => "Scheduled",
                                "archived" => "Archived",
                            ])
                            ->required(),
                        Forms\Components\Toggle::make("is_featured")
                            ->label("Featured"),
                        Forms\Components\DateTimePicker::make("published_at")
                            ->label("Publish Date"),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update($data);
                    }),

                Tables\Actions\Action::make("duplicate")
                    ->label("Duplicate")
                    ->icon("heroicon-o-document-duplicate")
                    ->color("gray")
                    ->action(function ($record) {
                        $newPage = $record->replicate();
                        $newPage->title .= " (Copy)";
                        $newPage->slug .= "-copy-" . time();
                        $newPage->status = "draft";
                        $newPage->published_at = null;
                        $newPage->save();

                        $newPage->categories()->sync($record->categories->pluck("id"));

                        foreach ($record->contentBlocks as $block) {
                            $newBlock = $block->replicate();
                            $newBlock->page_id = $newPage->id;
                            $newBlock->save();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading("Duplicate Page")
                    ->modalDescription("Are you sure you want to duplicate this page?"),

                Tables\Actions\Action::make("preview")
                    ->label("Preview")
                    ->icon("heroicon-o-eye")
                    ->color("info")
                    ->url(fn ($record) => $record->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status === 'published' || $record->status === 'draft'),

                Tables\Actions\EditAction::make()
                    ->icon("heroicon-o-pencil"),

                Tables\Actions\DeleteAction::make()
                    ->icon("heroicon-o-trash"),

                Tables\Actions\RestoreAction::make()
                    ->icon("heroicon-o-arrow-uturn-left"),

                Tables\Actions\ForceDeleteAction::make()
                    ->icon("heroicon-o-x-mark"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make("publish")
                        ->label("Publish Selected")
                        ->icon("heroicon-o-eye")
                        ->color("success")
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update([
                                    "status" => "published",
                                    "published_at" => $record->published_at ?? now(),
                                ]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading("Publish Pages")
                        ->modalDescription("Are you sure you want to publish the selected pages?"),

                    Tables\Actions\BulkAction::make("unpublish")
                        ->label("Unpublish Selected")
                        ->icon("heroicon-o-eye-slash")
                        ->color("warning")
                        ->action(function (Collection $records) {
                            $records->each->update(["status" => "draft"]);
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make("feature")
                        ->label("Mark as Featured")
                        ->icon("heroicon-o-star")
                        ->color("warning")
                        ->action(function (Collection $records) {
                            $records->each->update(["is_featured" => true]);
                        }),

                    Tables\Actions\BulkAction::make("unfeature")
                        ->label("Remove from Featured")
                        ->icon("heroicon-o-star")
                        ->color("gray")
                        ->action(function (Collection $records) {
                            $records->each->update(["is_featured" => false]);
                        }),

                    Tables\Actions\BulkAction::make("change_template")
                        ->label("Change Template")
                        ->icon("heroicon-o-document")
                        ->color("info")
                        ->form([
                            Forms\Components\Select::make("template")
                                ->options(function () {
                                    return collect(config("cms.templates", []))
                                        ->mapWithKeys(fn ($template, $key) => [$key => $template["name"]]);
                                })
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(["template" => $data["template"]]);
                        }),

                    Tables\Actions\BulkAction::make("assign_categories")
                        ->label("Assign Categories")
                        ->icon("heroicon-o-tag")
                        ->color("info")
                        ->form([
                            Forms\Components\CheckboxList::make("categories")
                                ->options(
                                    \App\Models\CmsCategory::query()
                                        ->where("is_active", true)
                                        ->orderBy("name")
                                        ->pluck("name", "id")
                                )
                                ->columns(2)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->categories()->sync($data["categories"]);
                            });
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->icon("heroicon-o-trash"),

                    Tables\Actions\RestoreBulkAction::make()
                        ->icon("heroicon-o-arrow-uturn-left"),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->icon("heroicon-o-x-mark"),
                ]),
            ])
            ->defaultSort("created_at", "desc")
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->poll("30s")
            ->deferLoading();
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
            "index" => Pages\ListPages::route("/"),
            "create" => Pages\CreatePage::route("/create"),
            "edit" => Pages\EditPage::route("/{record}/edit"),
        ];
    }
}
