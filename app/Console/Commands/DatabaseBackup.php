<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--reason= : Reason for backup (e.g., before-import, manual)}
                            {--list : List all available backups}
                            {--stats : Show backup statistics}
                            {--check : Check backup availability}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        // Check backup availability
        if ($this->option('check')) {
            return $this->checkAvailability($backupService);
        }

        // List backups
        if ($this->option('list')) {
            return $this->listBackups($backupService);
        }

        // Show statistics
        if ($this->option('stats')) {
            return $this->showStatistics($backupService);
        }

        // Create backup
        return $this->createBackup($backupService);
    }

    /**
     * Create a new backup
     */
    protected function createBackup(DatabaseBackupService $backupService): int
    {
        $reason = $this->option('reason');

        $this->info('Creating database backup...');

        if ($reason) {
            $this->line("Reason: {$reason}");
        }

        $result = $backupService->createBackup($reason);

        if (!$result['success']) {
            $this->error('❌ Backup failed!');
            $this->error($result['error']);
            return 1;
        }

        $this->newLine();
        $this->info('✅ Backup created successfully!');
        $this->table(
            ['Property', 'Value'],
            [
                ['Filename', $result['filename']],
                ['Size', $result['size_mb'] . ' MB'],
                ['Duration', $result['duration_seconds'] . ' seconds'],
                ['Created At', $result['created_at']],
            ]
        );

        return 0;
    }

    /**
     * List all available backups
     */
    protected function listBackups(DatabaseBackupService $backupService): int
    {
        $backups = $backupService->listBackups();

        if (empty($backups)) {
            $this->warn('No backups found.');
            return 0;
        }

        $this->info(sprintf('Found %d backup(s):', count($backups)));
        $this->newLine();

        $rows = array_map(function ($backup) {
            return [
                $backup['filename'],
                $backup['size_mb'] . ' MB',
                $backup['created_at']->format('Y-m-d H:i:s'),
                $backup['age_days'] . ' days ago',
            ];
        }, $backups);

        $this->table(
            ['Filename', 'Size', 'Created At', 'Age'],
            $rows
        );

        return 0;
    }

    /**
     * Show backup statistics
     */
    protected function showStatistics(DatabaseBackupService $backupService): int
    {
        $stats = $backupService->getStatistics();

        $this->info('Backup Statistics:');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Backups', $stats['total_backups']],
                ['Total Size', $stats['total_size_mb'] . ' MB'],
                ['Retention Policy', $stats['retention_days'] . ' days'],
                ['Oldest Backup', $stats['oldest_backup'] ?? 'N/A'],
                ['Newest Backup', $stats['newest_backup'] ?? 'N/A'],
            ]
        );

        return 0;
    }

    /**
     * Check backup availability
     */
    protected function checkAvailability(DatabaseBackupService $backupService): int
    {
        $check = $backupService->checkAvailability();

        if ($check['available']) {
            $this->info('✅ ' . $check['message']);
            $this->line('mysqldump: ' . $check['mysqldump_path']);
            $this->line('mysql: ' . $check['mysql_path']);
            return 0;
        } else {
            $this->error('❌ ' . $check['message']);
            return 1;
        }
    }
}
