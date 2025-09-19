<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsAuditLogResource\Pages;
use App\Models\CmsAuditLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CmsAuditLogResource extends Resource
{
    protected static ?string $model = CmsAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'CMS Management';

    protected static ?string $navigationLabel = 'Audit Log';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('event')
                    ->required()
                    ->disabled(),
                    
                Forms\Components\TextInput::make('user.name')
                    ->label('User')
                    ->disabled(),
                    
                Forms\Components\TextInput::make('auditable_type')
                    ->label('Resource Type')
                    ->disabled(),
                    
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
                    
                Forms\Components\Textarea::make('description')
                    ->disabled()
                    ->columnSpanFull(),
                    
                Forms\Components\KeyValue::make('old_values')
                    ->label('Old Values')
                    ->disabled()
                    ->columnSpanFull(),
                    
                Forms\Components\KeyValue::make('new_values')
                    ->label('New Values')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CmsAuditLog::EVENT_FAILED_LOGIN, CmsAuditLog::EVENT_PERMISSION_DENIED => 'danger',
                        CmsAuditLog::EVENT_ROLE_ASSIGNED, CmsAuditLog::EVENT_CONTENT_CREATED => 'success',
                        CmsAuditLog::EVENT_ROLE_REMOVED, CmsAuditLog::EVENT_CONTENT_DELETED => 'warning',
                        default => 'info',
                    }),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),
                    
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Resource')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'N/A'),
                    
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        CmsAuditLog::EVENT_ROLE_ASSIGNED => 'Role Assigned',
                        CmsAuditLog::EVENT_ROLE_REMOVED => 'Role Removed',
                        CmsAuditLog::EVENT_PERMISSION_CHANGED => 'Permission Changed',
                        CmsAuditLog::EVENT_CONTENT_ACCESSED => 'Content Accessed',
                        CmsAuditLog::EVENT_CONTENT_CREATED => 'Content Created',
                        CmsAuditLog::EVENT_CONTENT_UPDATED => 'Content Updated',
                        CmsAuditLog::EVENT_CONTENT_DELETED => 'Content Deleted',
                        CmsAuditLog::EVENT_CONTENT_PUBLISHED => 'Content Published',
                        CmsAuditLog::EVENT_WORKFLOW_SUBMITTED => 'Workflow Submitted',
                        CmsAuditLog::EVENT_WORKFLOW_APPROVED => 'Workflow Approved',
                        CmsAuditLog::EVENT_WORKFLOW_REJECTED => 'Workflow Rejected',
                        CmsAuditLog::EVENT_LOGIN => 'Login',
                        CmsAuditLog::EVENT_LOGOUT => 'Logout',
                        CmsAuditLog::EVENT_FAILED_LOGIN => 'Failed Login',
                        CmsAuditLog::EVENT_PERMISSION_DENIED => 'Permission Denied',
                        CmsAuditLog::EVENT_MEDIA_UPLOADED => 'Media Uploaded',
                        CmsAuditLog::EVENT_MEDIA_DELETED => 'Media Deleted',
                        CmsAuditLog::EVENT_SETTINGS_CHANGED => 'Settings Changed',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                    
                Filter::make('security_events')
                    ->label('Security Events Only')
                    ->query(fn (Builder $query): Builder => $query->securityEvents()),
                    
                Filter::make('content_events')
                    ->label('Content Events Only')
                    ->query(fn (Builder $query): Builder => $query->contentEvents()),
                    
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCmsAuditLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasCmsPermission('cms.system.settings') ?? false;
    }
}
