<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsRoleResource\Pages;
use App\Filament\Resources\CmsRoleResource\RelationManagers;
use App\Models\CmsRole;
use App\Models\CmsPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;

class CmsRoleResource extends Resource
{
    protected static ?string $model = CmsRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CMS Management';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('Unique role identifier (lowercase, no spaces)')
                            ->placeholder('e.g., content-editor'),

                        Forms\Components\TextInput::make('display_name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Human-readable role name')
                            ->placeholder('e.g., Content Editor'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->helperText('Brief description of this role\'s responsibilities'),

                        Forms\Components\Select::make('level')
                            ->required()
                            ->options([
                                1 => '1 - Viewer',
                                10 => '10 - Contributor',
                                20 => '20 - Author',
                                40 => '40 - Editor',
                                60 => '60 - Reviewer',
                                80 => '80 - Manager',
                                100 => '100 - Super Admin',
                            ])
                            ->default(1)
                            ->helperText('Higher levels inherit permissions from lower levels'),
                    ])->columns(2),

                Section::make('Role Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_system_role')
                            ->helperText('System roles cannot be deleted')
                            ->disabled(fn (?CmsRole $record) => $record?->is_system_role),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive roles cannot be assigned to users'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('System Name')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '100' => 'danger',
                        '80' => 'warning',
                        '60' => 'info',
                        '40' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\IconColumn::make('is_system_role')
                    ->label('System Role')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
                    
                Tables\Filters\TernaryFilter::make('is_system_role')
                    ->label('System Role')
                    ->boolean()
                    ->trueLabel('System roles only')
                    ->falseLabel('Custom roles only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (CmsRole $record) {
                        if ($record->is_system_role) {
                            throw new \Exception('System roles cannot be deleted');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $systemRoles = $records->where('is_system_role', true);
                            if ($systemRoles->count() > 0) {
                                throw new \Exception('System roles cannot be deleted');
                            }
                        }),
                ]),
            ])
            ->defaultSort('level', 'desc');
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
            'index' => Pages\ListCmsRoles::route('/'),
            'create' => Pages\CreateCmsRole::route('/create'),
            'edit' => Pages\EditCmsRole::route('/{record}/edit'),
        ];
    }
}
