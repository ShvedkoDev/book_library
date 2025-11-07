<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore
                            {filename? : Backup filename to restore}
                            {--latest : Restore from latest backup}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        // Get backup filename
        $filename = $this->argument('filename');

        if ($this->option('latest')) {
            $backups = $backupService->listBackups();

            if (empty($backups)) {
                $this->error('No backups found.');
                return 1;
            }

            $filename = $backups[0]['filename'];
            $this->info("Using latest backup: {$filename}");
        }

        if (!$filename) {
            // Show available backups and prompt
            $backups = $backupService->listBackups();

            if (empty($backups)) {
                $this->error('No backups found.');
                return 1;
            }

            $this->info('Available backups:');
            $this->newLine();

            $choices = [];
            foreach ($backups as $index => $backup) {
                $label = sprintf(
                    '%s (%s MB, %s)',
                    $backup['filename'],
                    $backup['size_mb'],
                    $backup['created_at']->format('Y-m-d H:i:s')
                );
                $choices[$index] = $label;
            }

            $selectedIndex = $this->choice(
                'Select a backup to restore:',
                $choices,
                0
            );

            // Extract index from choice
            $selectedKey = array_search($selectedIndex, $choices);
            $filename = $backups[$selectedKey]['filename'];
        }

        // Confirm restore
        if (!$this->option('force')) {
            $this->newLine();
            $this->warn('⚠️  WARNING: This will overwrite your current database!');
            $this->warn('All current data will be replaced with the backup data.');
            $this->newLine();

            if (!$this->confirm("Are you sure you want to restore from {$filename}?", false)) {
                $this->info('Restore cancelled.');
                return 0;
            }
        }

        // Perform restore
        $this->info('Restoring database...');
        $this->line("Backup: {$filename}");
        $this->newLine();

        $result = $backupService->restoreBackup($filename);

        if (!$result['success']) {
            $this->error('❌ Restore failed!');
            $this->error($result['error']);
            return 1;
        }

        $this->newLine();
        $this->info('✅ Database restored successfully!');
        $this->table(
            ['Property', 'Value'],
            [
                ['Filename', $result['filename']],
                ['Duration', $result['duration_seconds'] . ' seconds'],
                ['Restored At', $result['restored_at']],
            ]
        );

        $this->newLine();
        $this->warn('⚠️  You may need to clear application caches:');
        $this->line('php artisan cache:clear');
        $this->line('php artisan config:clear');
        $this->line('php artisan route:clear');

        return 0;
    }
}
