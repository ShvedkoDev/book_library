<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileUploadResource\Pages;
use App\Filament\Resources\FileUploadResource\RelationManagers;
use App\Models\FileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class FileUploadResource extends Resource
{
    protected static ?string $model = FileUpload::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $modelLabel = 'File Upload';

    protected static ?string $pluralModelLabel = 'File Uploads';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('File Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File')
                            ->required()
                            ->disk('local')
                            ->directory('uploads')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->acceptedFileTypes([]) // Accept all file types
                            ->maxSize(102400) // 100MB
                            ->helperText('Upload any file type. Maximum size: 100MB. Files will be stored in storage/app/uploads/')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Add a description for this file (optional)')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(fn () => auth()->id()),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('File name copied!')
                    ->tooltip('Click to copy'),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Size')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('file_size', $direction);
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('File Type')
                    ->options(function () {
                        return FileUpload::query()
                            ->distinct()
                            ->pluck('mime_type', 'mime_type')
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (FileUpload $record) {
                        return Storage::download($record->file_path, $record->original_name);
                    }),
                Tables\Actions\Action::make('copy_path')
                    ->label('Copy Path')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function (FileUpload $record) {
                        // This will be handled by JavaScript
                    })
                    ->modalContent(fn (FileUpload $record) => view('filament.modals.file-path', [
                        'relativePath' => $record->file_path,
                        'absolutePath' => $record->full_path,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected files? This will permanently remove them from storage.'),
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
            'index' => Pages\ListFileUploads::route('/'),
            'create' => Pages\CreateFileUpload::route('/create'),
            'edit' => Pages\EditFileUpload::route('/{record}/edit'),
        ];
    }
}
