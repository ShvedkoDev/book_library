<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-cleanup
                            {--days=30 : Number of days to keep backups (default: 30)}
                            {--force : Skip confirmation prompt}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old database backups based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $retentionDays = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info('Database Backup Cleanup');
        $this->line("Retention Policy: {$retentionDays} days");
        $this->newLine();

        // Get all backups
        $allBackups = $backupService->listBackups();

        if (empty($allBackups)) {
            $this->info('No backups found.');
            return 0;
        }

        // Find old backups
        $cutoffDate = now()->subDays($retentionDays);
        $oldBackups = array_filter($allBackups, function ($backup) use ($cutoffDate) {
            return $backup['created_at']->lt($cutoffDate);
        });

        if (empty($oldBackups)) {
            $this->info('No old backups to clean up.');
            return 0;
        }

        $totalSize = array_sum(array_column($oldBackups, 'size'));

        $this->warn(sprintf(
            'Found %d backup(s) older than %d days:',
            count($oldBackups),
            $retentionDays
        ));
        $this->newLine();

        $rows = array_map(function ($backup) {
            return [
                $backup['filename'],
                $backup['size_mb'] . ' MB',
                $backup['created_at']->format('Y-m-d H:i:s'),
                $backup['age_days'] . ' days',
            ];
        }, array_values($oldBackups));

        $this->table(
            ['Filename', 'Size', 'Created At', 'Age'],
            $rows
        );

        $this->newLine();
        $this->line(sprintf(
            'Total size to be freed: %.2f MB',
            $totalSize / 1024 / 1024
        ));
        $this->newLine();

        // Dry run mode
        if ($dryRun) {
            $this->info('[DRY RUN] No files were deleted.');
            $this->info('Remove --dry-run flag to actually delete these backups.');
            return 0;
        }

        // Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to delete these backups?', false)) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        // Perform cleanup
        $this->info('Cleaning up old backups...');
        $result = $backupService->cleanupOldBackups($retentionDays);

        $this->newLine();
        $this->info('✅ Cleanup completed successfully!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Deleted Backups', $result['deleted_count']],
                ['Space Freed', $result['deleted_size_mb'] . ' MB'],
            ]
        );

        return 0;
    }
}
