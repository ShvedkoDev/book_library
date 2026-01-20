<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackupService
{
    /**
     * Storage disk for backups
     */
    protected string $disk = 'local';

    /**
     * Storage directory for backups
     */
    protected string $directory = 'database-backups';

    /**
     * Backup retention days
     */
    protected int $retentionDays = 30;

    /**
     * Create a database backup
     *
     * @param string|null $reason Reason for backup (e.g., 'before-import')
     * @return array Backup information
     */
    public function createBackup(?string $reason = null): array
    {
        $startTime = microtime(true);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}" . ($reason ? "_{$reason}" : '') . '.sql';
        $filepath = storage_path("app/{$this->directory}/{$filename}");

        try {
            // Ensure directory exists
            if (!Storage::disk($this->disk)->exists($this->directory)) {
                Storage::disk($this->disk)->makeDirectory($this->directory);
            }

            // Prefer mysqldump if available, otherwise fallback to pure PHP dump
            $availability = $this->checkAvailability();
            if ($availability['available']) {
                // Get database configuration
                $database = config('database.connections.' . config('database.default'));
                $host = $database['host'];
                $port = $database['port'] ?? 3306;
                $dbName = $database['database'];
                $username = $database['username'];
                $password = $database['password'];

                // Build mysqldump command
                $command = sprintf(
                    'mysqldump --host=%s --port=%d --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
                    escapeshellarg($host),
                    $port,
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName),
                    escapeshellarg($filepath)
                );

                // Execute backup
                @exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    throw new \Exception('Backup failed: ' . implode("\n", $output));
                }
            } else {
                // Fallback: generate SQL dump via PHP only
                $this->generatePhpDump($filepath);
            }

            // Get file size
            $fileSize = file_exists($filepath) ? filesize($filepath) : 0;

            if ($fileSize === 0) {
                throw new \Exception('Backup file is empty');
            }

            $duration = microtime(true) - $startTime;

            $backupInfo = [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => $fileSize,
                'size_mb' => round($fileSize / 1024 / 1024, 2),
                'reason' => $reason,
                'created_at' => now()->toIso8601String(),
                'duration_seconds' => round($duration, 2),
            ];

            Log::info('Database backup created successfully', $backupInfo);

            return $backupInfo;

        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'filename' => $filename,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filename' => $filename,
            ];
        }
    }

    /**
     * Create backup before CSV import
     *
     * @param int|null $csvImportId CSV import ID
     * @return array Backup information
     */
    public function createBackupBeforeImport(?int $csvImportId = null): array
    {
        $reason = 'before-import';
        if ($csvImportId) {
            $reason .= "-{$csvImportId}";
        }

        return $this->createBackup($reason);
    }

    /**
     * Restore database from backup
     *
     * @param string $filename Backup filename
     * @return array Restore result
     */
    public function restoreBackup(string $filename): array
    {
        $startTime = microtime(true);
        $filepath = storage_path("app/{$this->directory}/{$filename}");

        try {
            if (!file_exists($filepath)) {
                throw new \Exception("Backup file not found: {$filename}");
            }

            $availability = $this->checkAvailability();
            if ($availability['available']) {
                // Get database configuration
                $database = config('database.connections.' . config('database.default'));
                $host = $database['host'];
                $port = $database['port'] ?? 3306;
                $dbName = $database['database'];
                $username = $database['username'];
                $password = $database['password'];

                // Build mysql restore command
                $command = sprintf(
                    'mysql --host=%s --port=%d --user=%s --password=%s %s < %s 2>&1',
                    escapeshellarg($host),
                    $port,
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName),
                    escapeshellarg($filepath)
                );

                // Execute restore
                @exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    throw new \Exception('Restore failed: ' . implode("\n", $output));
                }
            } else {
                // Fallback: restore via PHP
                $sql = file_get_contents($filepath);
                DB::unprepared($sql);
            }

            $duration = microtime(true) - $startTime;

            $restoreInfo = [
                'success' => true,
                'filename' => $filename,
                'restored_at' => now()->toIso8601String(),
                'duration_seconds' => round($duration, 2),
            ];

            Log::info('Database restored successfully', $restoreInfo);

            return $restoreInfo;

        } catch (\Exception $e) {
            Log::error('Database restore failed', [
                'error' => $e->getMessage(),
                'filename' => $filename,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filename' => $filename,
            ];
        }
    }

    /**
     * List all available backups
     *
     * @return array List of backups with metadata
     */
    public function listBackups(): array
    {
        $backups = [];

        if (!Storage::disk($this->disk)->exists($this->directory)) {
            return $backups;
        }

        $files = Storage::disk($this->disk)->files($this->directory);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.sql')) {
                continue;
            }

            $filepath = storage_path("app/{$file}");
            $filename = basename($file);

            $backups[] = [
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'size_mb' => round(filesize($filepath) / 1024 / 1024, 2),
                'created_at' => Carbon::createFromTimestamp(filemtime($filepath)),
                'age_days' => Carbon::createFromTimestamp(filemtime($filepath))->diffInDays(now()),
            ];
        }

        // Sort by created_at descending (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Clean up old backups based on retention policy
     *
     * @param int|null $retentionDays Days to keep (default: 30)
     * @return array Cleanup result
     */
    public function cleanupOldBackups(?int $retentionDays = null): array
    {
        $retentionDays = $retentionDays ?? $this->retentionDays;
        $cutoffDate = now()->subDays($retentionDays);

        $backups = $this->listBackups();
        $deletedCount = 0;
        $deletedSize = 0;
        $deletedFiles = [];

        foreach ($backups as $backup) {
            if ($backup['created_at']->lt($cutoffDate)) {
                try {
                    $filepath = $backup['filepath'];
                    if (file_exists($filepath)) {
                        $deletedSize += $backup['size'];
                        unlink($filepath);
                        $deletedFiles[] = $backup['filename'];
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old backup', [
                        'filename' => $backup['filename'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $result = [
            'deleted_count' => $deletedCount,
            'deleted_size_mb' => round($deletedSize / 1024 / 1024, 2),
            'deleted_files' => $deletedFiles,
            'retention_days' => $retentionDays,
        ];

        if ($deletedCount > 0) {
            Log::info('Cleaned up old database backups', $result);
        }

        return $result;
    }

    /**
     * Get backup statistics
     *
     * @return array Backup statistics
     */
    public function getStatistics(): array
    {
        $backups = $this->listBackups();

        $totalSize = array_sum(array_column($backups, 'size'));
        $oldestBackup = !empty($backups) ? end($backups)['created_at'] : null;
        $newestBackup = !empty($backups) ? $backups[0]['created_at'] : null;

        return [
            'total_backups' => count($backups),
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'oldest_backup' => $oldestBackup?->toIso8601String(),
            'newest_backup' => $newestBackup?->toIso8601String(),
            'retention_days' => $this->retentionDays,
        ];
    }

    /**
     * Check if backup functionality is available
     *
     * @return array Availability check result
     */
    public function checkAvailability(): array
    {
        // First check if shell_exec is available
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            return [
                'available' => false,
                'mysqldump_path' => '',
                'mysql_path' => '',
                'message' => 'shell_exec() is disabled on this server. Using pure PHP dump/restore.',
            ];
        }

        // Check if shell_exec is in disabled functions
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        if (in_array('shell_exec', $disabled) || in_array('exec', $disabled)) {
            return [
                'available' => false,
                'mysqldump_path' => '',
                'mysql_path' => '',
                'message' => 'shell_exec() or exec() is disabled. Using pure PHP dump/restore.',
            ];
        }

        // Now it's safe to call shell_exec
        $mysqldump = @shell_exec('which mysqldump 2>/dev/null');
        $mysql = @shell_exec('which mysql 2>/dev/null');

        $available = !empty($mysqldump) && !empty($mysql);

        return [
            'available' => $available,
            'mysqldump_path' => trim($mysqldump ?? ''),
            'mysql_path' => trim($mysql ?? ''),
            'message' => $available
                ? 'Backup functionality is available'
                : 'mysqldump or mysql command not found. Falling back to pure PHP dump/restore.',
        ];
    }

    /**
     * Generate SQL dump via PHP when mysqldump is unavailable
     */
    protected function generatePhpDump(string $filepath): void
    {
        // Ensure directory exists when falling back to PHP
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $pdo = DB::connection()->getPdo();
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $fh = fopen($filepath, 'w');
        if (!$fh) {
            throw new \RuntimeException('Unable to open backup file for writing: ' . $filepath);
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            // Table structure
            $createStmt = $pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'] ?? '';
            fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n");

            // Table data
            $rows = $pdo->query('SELECT * FROM `'.$table.'`');
            while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
                $columns = array_map(fn($c) => '`'.$c.'`', array_keys($row));
                $values = array_map(function ($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote($v);
                }, array_values($row));
                $insert = 'INSERT INTO `'.$table.'` ('.implode(',', $columns).') VALUES ('.implode(',', $values).');';
                fwrite($fh, $insert."\n");
            }
            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }
}
