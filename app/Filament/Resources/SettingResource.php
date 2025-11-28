<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Setting key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Unique identifier for this setting'),

                Forms\Components\Select::make('group')
                    ->label('Group')
                    ->options([
                        'general' => 'General',
                        'library' => 'Library',
                        'features' => 'Features',
                        'analytics' => 'Analytics',
                        'email' => 'Email',
                        'maintenance' => 'Maintenance',
                        'system' => 'System',
                    ])
                    ->required()
                    ->default('general'),

                Forms\Components\Select::make('type')
                    ->label('Value type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text (Long)',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'json' => 'JSON',
                    ])
                    ->required()
                    ->default('string')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('value', '')),

                // Boolean input
                Forms\Components\Toggle::make('value')
                    ->label('Value')
                    ->onColor('success')
                    ->offColor('danger')
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'boolean')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('type') === 'boolean')
                    ->dehydrateStateUsing(fn ($state) => $state ? 'true' : 'false')
                    ->afterStateHydrated(function (Forms\Components\Toggle $component, $state) {
                        $component->state($state === 'true' || $state === '1' || $state === 1);
                    }),

                // Integer input
                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    ->numeric()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'integer')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('type') === 'integer'),

                // String input
                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'string')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('type') === 'string'),

                // Text (long) input
                Forms\Components\Textarea::make('value')
                    ->label('Value')
                    ->rows(3)
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'text')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('type') === 'text'),

                // JSON input
                Forms\Components\Textarea::make('value')
                    ->label('Value (json)')
                    ->rows(5)
                    ->columnSpanFull()
                    ->helperText('Enter valid JSON format')
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'json')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('type') === 'json'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Explain what this setting controls'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(80)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->description)
                    ->wrap(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'library' => 'success',
                        'features' => 'primary',
                        'analytics' => 'info',
                        'email' => 'info',
                        'maintenance' => 'danger',
                        'system' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'library' => 'Library',
                        'features' => 'Features',
                        'analytics' => 'Analytics',
                        'email' => 'Email',
                        'maintenance' => 'Maintenance',
                        'system' => 'System',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group');
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
