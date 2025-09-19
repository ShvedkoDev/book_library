<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsContentWorkflowResource\Pages;
use App\Filament\Resources\CmsContentWorkflowResource\RelationManagers;
use App\Models\CmsContentWorkflow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CmsContentWorkflowResource extends Resource
{
    protected static ?string $model = CmsContentWorkflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('workflowable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('workflowable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('previous_status'),
                Forms\Components\Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required(),
                Forms\Components\Select::make('reviewer_id')
                    ->relationship('reviewer', 'name'),
                Forms\Components\Select::make('approver_id')
                    ->relationship('approver', 'name'),
                Forms\Components\DateTimePicker::make('submitted_at'),
                Forms\Components\DateTimePicker::make('reviewed_at'),
                Forms\Components\DateTimePicker::make('approved_at'),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\Textarea::make('review_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('revision_history'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workflowable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('workflowable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('previous_status'),
                Tables\Columns\TextColumn::make('author.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCmsContentWorkflows::route('/'),
            'create' => Pages\CreateCmsContentWorkflow::route('/create'),
            'edit' => Pages\EditCmsContentWorkflow::route('/{record}/edit'),
        ];
    }
}
