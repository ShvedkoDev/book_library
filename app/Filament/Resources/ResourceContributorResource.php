<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResourceContributorResource\Pages;
use App\Models\ResourceContributor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResourceContributorResource extends Resource
{
    protected static ?string $model = ResourceContributor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Contributors';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('organization')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255)
                            ->prefix('https://')
                            ->suffixIcon('heroicon-m-globe-alt'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('contributor-logos')
                            ->imageEditor()
                            ->helperText('Upload an image (max 2MB)'),
                    ]),

                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Toggle to show/hide contributor on pages'),
                        Forms\Components\TextInput::make('order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Order in contributor listings'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-logo.png')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ResourceContributor $record): string => $record->organization ?? ''),
                Tables\Columns\TextColumn::make('website_url')
                    ->label('Website')
                    ->url(fn (ResourceContributor $record): ?string => $record->website_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-link')
                    ->color('primary')
                    ->limit(30),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All contributors')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewPages')
                    ->label('View pages')
                    ->icon('heroicon-m-document-text')
                    ->modalHeading(fn (ResourceContributor $record) => "Pages using: {$record->name}")
                    ->modalContent(function (ResourceContributor $record) {
                        $pages = $record->pages()->get();
                        return view('filament.resources.resource-contributor-resource.pages-list', [
                            'pages' => $pages,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn (ResourceContributor $record) => $record->pages()->count() > 0),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageResourceContributors::route('/'),
        ];
    }
}
