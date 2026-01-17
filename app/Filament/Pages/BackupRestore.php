<?php

namespace App\Filament\Pages;

use App\Services\AppBackupService;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class BackupRestore extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 98;
    protected static ?string $title = 'Backup & Restore';
    protected static string $view = 'filament.pages.backup-restore';
    protected static bool $shouldRegisterNavigation = true;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create Backup')
                    ->description('Archive storage files and database dump into a single ZIP.')
                    ->schema([
                        Placeholder::make('info')
                            ->content('Click "Create Backup ZIP" to generate and download the archive.'),
                    ]),

                Section::make('Restore Backup')
                    ->description('Upload a previously generated ZIP and restore files & database.')
                    ->schema([
                        FileUpload::make('backup_zip')
                            ->label('Backup ZIP')
                            ->disk('local')
                            ->directory('full-backups/uploads')
                            ->acceptedFileTypes(['application/zip'])
                            ->maxSize(512000) // 500MB
                            ->preserveFilenames()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function createBackup(): void
    {
        $service = new AppBackupService();
        $result = $service->createFullBackup('manual');
        if (!($result['success'] ?? false)) {
            Notification::make()->danger()->title('Backup failed')->body($result['error'] ?? 'Unknown error')->send();
            return;
        }

        $downloadName = $result['download_name'] ?? 'app_backup.tar';
        $url = route('admin.backups.download', ['file' => $downloadName]);
        Notification::make()->success()->title('Backup created')
            ->body("Backup archive is ready. Click to download.")
            ->actions([
                \Filament\Notifications\Actions\Action::make('download')->button()->url($url)->openUrlInNewTab(),
            ])->persistent()->send();
    }

    public function restore(): void
    {
        $zip = $this->data['backup_zip'] ?? null;
        if (!$zip) {
            Notification::make()->warning()->title('No ZIP selected')->body('Please upload a backup ZIP.')->send();
            return;
        }

        $zipPath = Storage::disk('local')->path($zip);
        $service = new AppBackupService();
        $result = $service->restoreFromZip($zipPath);
        if (!($result['success'] ?? false)) {
            Notification::make()->danger()->title('Restore failed')->body($result['error'] ?? 'Unknown error')->send();
            return;
        }

        Notification::make()->success()->title('Restore completed')->body('Files and database restored.')->persistent()->send();
    }
}
