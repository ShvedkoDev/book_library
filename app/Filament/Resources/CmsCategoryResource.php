<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsCategoryResource\Pages;
use App\Filament\Resources\CmsCategoryResource\RelationManagers;
use App\Models\CmsCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CmsCategoryResource extends Resource
{
    protected static ?string $model = CmsCategory::class;

    protected static ?string $navigationIcon = "heroicon-o-folder-open";

    protected static ?string $navigationGroup = "CMS";

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = "name";

    protected static ?string $navigationLabel = "Categories";

    protected static ?string $modelLabel = "Category";

    protected static ?string $pluralModelLabel = "Categories";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Basic Information")
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make("name")
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                        if ($context === "edit") {
                                            return;
                                        }
                                        $set("slug", Str::slug($state));
                                    })
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make("slug")
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(CmsCategory::class, "slug", ignoreRecord: true)
                                    ->rules(["regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/"])
                                    ->helperText("URL-friendly version of the name")
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Select::make("parent_id")
                            ->label("Parent Category")
                            ->relationship("parent", "name")
                            ->searchable()
                            ->preload()
                            ->helperText("Select a parent category to create a hierarchy"),

                        Forms\Components\RichEditor::make("description")
                            ->columnSpanFull()
                            ->toolbarButtons([
                                "bold",
                                "bulletList",
                                "italic",
                                "link",
                                "orderedList",
                                "redo",
                                "strike",
                                "underline",
                                "undo",
                            ]),
                    ]),

                Forms\Components\Section::make("Appearance & Settings")
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\ColorPicker::make("color")
                                    ->label("Category Color")
                                    ->default("#3B82F6")
                                    ->helperText("Used for category badges and theming"),

                                Forms\Components\Toggle::make("is_active")
                                    ->label("Active")
                                    ->default(true)
                                    ->helperText("Whether this category is visible"),

                                Forms\Components\TextInput::make("sort_order")
                                    ->label("Sort Order")
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText("Order within parent category"),
                            ]),
                    ]),

                Forms\Components\Section::make("SEO Settings")
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make("seo_title")
                            ->label("SEO Title")
                            ->maxLength(60)
                            ->helperText("Recommended: 50-60 characters"),

                        Forms\Components\Textarea::make("seo_description")
                            ->label("SEO Description")
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText("Recommended: 150-160 characters"),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make("color")
                    ->tooltip("Category Color"),

                Tables\Columns\TextColumn::make("name")
                    ->weight("medium")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make("is_active")
                    ->label("Status")
                    ->colors([
                        "success" => true,
                        "danger" => false,
                    ])
                    ->formatStateUsing(fn ($state): string => $state ? "Active" : "Inactive"),

                Tables\Columns\TextColumn::make("parent.name")
                    ->label("Parent")
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("sort_order")
                    ->label("Order")
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label("Created")
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort("sort_order", "asc")
            ->reorderable("sort_order")
            ->filters([
                Tables\Filters\SelectFilter::make("parent_id")
                    ->label("Parent Category")
                    ->relationship("parent", "name")
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make("is_active")
                    ->label("Active Only")
                    ->query(fn (Builder $query): Builder => $query->where("is_active", true))
                    ->toggle(),

                Tables\Filters\Filter::make("root_categories")
                    ->label("Root Categories Only")
                    ->query(fn (Builder $query): Builder => $query->whereNull("parent_id"))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make("add_child")
                    ->label("Add Child")
                    ->icon("heroicon-o-plus-circle")
                    ->color("success")
                    ->url(fn ($record) => static::getUrl("create", ["parent_id" => $record->id]))
                    ->visible(fn ($record) => $record->is_active),

                Tables\Actions\EditAction::make()
                    ->icon("heroicon-o-pencil"),

                Tables\Actions\DeleteAction::make()
                    ->icon("heroicon-o-trash"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make("activate")
                        ->label("Activate Selected")
                        ->icon("heroicon-o-check-circle")
                        ->color("success")
                        ->action(function (Collection $records) {
                            $records->each->update(["is_active" => true]);
                        }),

                    Tables\Actions\BulkAction::make("deactivate")
                        ->label("Deactivate Selected")
                        ->icon("heroicon-o-x-circle")
                        ->color("warning")
                        ->action(function (Collection $records) {
                            $records->each->update(["is_active" => false]);
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->icon("heroicon-o-trash"),
                ]),
            ])
            ->striped();
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
            "index" => Pages\ListCmsCategories::route("/"),
            "create" => Pages\CreateCmsCategory::route("/create"),
            "edit" => Pages\EditCmsCategory::route("/{record}/edit"),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where("is_active", true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return "primary";
    }
}
