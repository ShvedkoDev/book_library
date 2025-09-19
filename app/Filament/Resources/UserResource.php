<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\CmsRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'CMS Management';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('department')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('bio')
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('CMS Access')
                    ->schema([
                        Forms\Components\Toggle::make('is_cms_user')
                            ->label('CMS User')
                            ->helperText('Allow this user to access the CMS'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Active users can log in'),

                        Select::make('cms_roles')
                            ->label('CMS Roles')
                            ->multiple()
                            ->relationship('cmsRoles', 'display_name')
                            ->options(CmsRole::where('is_active', true)->pluck('display_name', 'id'))
                            ->helperText('Assign CMS roles to this user'),
                    ])->columns(2),

                Section::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8),
                    ])
                    ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_cms_user')
                    ->label('CMS User')
                    ->boolean(),

                Tables\Columns\TextColumn::make('cmsRoles.display_name')
                    ->label('CMS Roles')
                    ->badge()
                    ->separator(',')
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_cms_access')
                    ->label('Last CMS Access')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_cms_user')
                    ->label('CMS Users')
                    ->options([
                        1 => 'CMS Users Only',
                        0 => 'Non-CMS Users Only',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active Only',
                        0 => 'Inactive Only',
                    ]),

                SelectFilter::make('cms_roles')
                    ->label('CMS Role')
                    ->relationship('cmsRoles', 'display_name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('assign_role')
                    ->label('Assign Role')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('role_id')
                            ->label('Role')
                            ->options(CmsRole::where('is_active', true)->pluck('display_name', 'id'))
                            ->required(),
                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Expires At')
                            ->helperText('Leave empty for permanent assignment'),
                    ])
                    ->action(function (User $record, array $data) {
                        $role = CmsRole::find($data['role_id']);
                        $record->assignCmsRole($role, auth()->user(), $data['expires_at'] ? now()->parse($data['expires_at']) : null);
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        if ($record->id === auth()->id()) {
                            throw new \Exception('You cannot delete your own account');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            if ($records->contains('id', auth()->id())) {
                                throw new \Exception('You cannot delete your own account');
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasCmsPermission('cms.users.view') ?? false;
    }
}
