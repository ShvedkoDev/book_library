<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AppBackupService
{
    protected string $disk = 'local';
    protected string $baseDir = 'full-backups';

    /**
     * Maximum size per archive part in bytes (default: 2GB)
     * Adjust this based on your hosting limits
     */
    protected int $maxPartSize = 2 * 1024 * 1024 * 1024; // 2GB

    public function createFullBackup(?string $reason = null): array
    {
        // Prevent timeout on shared hosting
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $label = $reason ? '_'.$reason : '';
        $baseName = "app_backup_{$timestamp}{$label}";
        $zipName = $baseName . '.zip';
        $zipPath = storage_path('app/'.$this->baseDir.'/'.$zipName);
        $dirPath = storage_path('app/'.$this->baseDir.'/'.$baseName);

        // Ensure backup root and folder exist
        if (!Storage::disk($this->disk)->exists($this->baseDir)) {
            Storage::disk($this->disk)->makeDirectory($this->baseDir);
        }
        if (!is_dir($dirPath)) {
            @mkdir($dirPath, 0775, true);
        }

        $dbService = new DatabaseBackupService();
        $dbInfo = $dbService->createBackup($reason);
        if (!($dbInfo['success'] ?? false)) {
            return ['success' => false, 'error' => $dbInfo['error'] ?? 'Database backup failed'];
        }

        // Build folder structure
        $this->copyIntoDir($dbInfo['filepath'], $dirPath.'/database/backup.sql');
        $this->copyDirectory(storage_path('app/public'), $dirPath.'/storage/public');
        $this->copyDirectory(storage_path('app/uploads'), $dirPath.'/storage/uploads');
        if (file_exists(base_path('.env.example'))) $this->copyIntoDir(base_path('.env.example'), $dirPath.'/config/env.example');
        if (file_exists(base_path('composer.json'))) $this->copyIntoDir(base_path('composer.json'), $dirPath.'/config/composer.json');
        if (file_exists(base_path('composer.lock'))) $this->copyIntoDir(base_path('composer.lock'), $dirPath.'/config/composer.lock');

        // If ZipArchive exists, also build a zip for convenience
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $this->addDirectoryToZip($zip, $dirPath, $baseName);
                $zip->close();
                return [
                    'success' => true,
                    'download_name' => $zipName,
                    'zip_path' => $zipPath,
                    'dir_path' => $dirPath,
                ];
            }
        }

        // Fall back to returning the folder; download route will stream a TAR on-the-fly
        return [
            'success' => true,
            'download_name' => $baseName . '.tar',
            'zip_path' => null,
            'dir_path' => $dirPath,
        ];
    }

    public function createFilesOnlyBackup(?string $reason = null): array
    {
        // Prevent timeout on shared hosting
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $label = $reason ? '_'.$reason : '';
        $baseName = "files_backup_{$timestamp}{$label}";
        $zipName = $baseName . '.zip';
        $zipPath = storage_path('app/'.$this->baseDir.'/'.$zipName);
        $dirPath = storage_path('app/'.$this->baseDir.'/'.$baseName);

        // Ensure backup root and folder exist
        if (!Storage::disk($this->disk)->exists($this->baseDir)) {
            Storage::disk($this->disk)->makeDirectory($this->baseDir);
        }
        if (!is_dir($dirPath)) {
            @mkdir($dirPath, 0775, true);
        }

        // Build folder structure (files only, no database)
        $this->copyDirectory(storage_path('app/public'), $dirPath.'/storage/public');
        $this->copyDirectory(storage_path('app/uploads'), $dirPath.'/storage/uploads');
        if (file_exists(base_path('.env.example'))) $this->copyIntoDir(base_path('.env.example'), $dirPath.'/config/env.example');
        if (file_exists(base_path('composer.json'))) $this->copyIntoDir(base_path('composer.json'), $dirPath.'/config/composer.json');
        if (file_exists(base_path('composer.lock'))) $this->copyIntoDir(base_path('composer.lock'), $dirPath.'/config/composer.lock');

        // If ZipArchive exists, also build a zip for convenience
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $this->addDirectoryToZip($zip, $dirPath, $baseName);
                $zip->close();
                return [
                    'success' => true,
                    'download_name' => $zipName,
                    'zip_path' => $zipPath,
                    'dir_path' => $dirPath,
                ];
            }
        }

        // Fall back to returning the folder; download route will stream a TAR on-the-fly
        return [
            'success' => true,
            'download_name' => $baseName . '.tar',
            'zip_path' => null,
            'dir_path' => $dirPath,
        ];
    }

    /**
     * Create split backup with multiple archive files
     * Each part will be approximately maxPartSize (default: 2GB)
     */
    public function createSplitBackup(?string $reason = null): array
    {
        // Prevent timeout on shared hosting
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'error' => 'ZipArchive extension required for split backups'];
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $label = $reason ? '_'.$reason : '';
        $baseName = "split_backup_{$timestamp}{$label}";
        $backupDir = storage_path('app/'.$this->baseDir.'/'.$baseName);

        // Ensure backup directory exists
        if (!Storage::disk($this->disk)->exists($this->baseDir)) {
            Storage::disk($this->disk)->makeDirectory($this->baseDir);
        }
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0775, true);
        }

        $createdParts = [];
        $totalSize = 0;

        // Part 1: Database backup
        $dbService = new DatabaseBackupService();
        $dbInfo = $dbService->createBackup($reason);
        if (!($dbInfo['success'] ?? false)) {
            return ['success' => false, 'error' => $dbInfo['error'] ?? 'Database backup failed'];
        }

        $dbZipPath = $backupDir . '/part1_database.zip';
        $zip = new \ZipArchive();
        if ($zip->open($dbZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $zip->addFile($dbInfo['filepath'], 'database/backup.sql');
            $zip->close();
            $dbSize = filesize($dbZipPath);
            $createdParts[] = [
                'filename' => 'part1_database.zip',
                'path' => $dbZipPath,
                'size' => $dbSize,
                'size_mb' => round($dbSize / 1024 / 1024, 2),
                'type' => 'database',
            ];
            $totalSize += $dbSize;
        }

        // Part 2+: Config files
        $configZipPath = $backupDir . '/part2_config.zip';
        $zip = new \ZipArchive();
        if ($zip->open($configZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            if (file_exists(base_path('.env.example'))) {
                $zip->addFile(base_path('.env.example'), 'config/env.example');
            }
            if (file_exists(base_path('composer.json'))) {
                $zip->addFile(base_path('composer.json'), 'config/composer.json');
            }
            if (file_exists(base_path('composer.lock'))) {
                $zip->addFile(base_path('composer.lock'), 'config/composer.lock');
            }
            $zip->close();
            $configSize = filesize($configZipPath);
            $createdParts[] = [
                'filename' => 'part2_config.zip',
                'path' => $configZipPath,
                'size' => $configSize,
                'size_mb' => round($configSize / 1024 / 1024, 2),
                'type' => 'config',
            ];
            $totalSize += $configSize;
        }

        // Part 3+: Storage public directory (split if needed)
        $publicParts = $this->createSplitZipForDirectory(
            storage_path('app/public'),
            $backupDir,
            'part3_storage_public',
            'storage/public'
        );
        foreach ($publicParts as $part) {
            $createdParts[] = $part;
            $totalSize += $part['size'];
        }

        // Part N+: Storage uploads directory (split if needed)
        $partNumber = 3 + count($publicParts);
        $uploadParts = $this->createSplitZipForDirectory(
            storage_path('app/uploads'),
            $backupDir,
            "part{$partNumber}_storage_uploads",
            'storage/uploads'
        );
        foreach ($uploadParts as $part) {
            $createdParts[] = $part;
            $totalSize += $part['size'];
        }

        // Create a manifest file
        $manifestPath = $backupDir . '/MANIFEST.json';
        file_put_contents($manifestPath, json_encode([
            'created_at' => now()->toIso8601String(),
            'reason' => $reason,
            'total_parts' => count($createdParts),
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'total_size_gb' => round($totalSize / 1024 / 1024 / 1024, 2),
            'max_part_size_gb' => round($this->maxPartSize / 1024 / 1024 / 1024, 2),
            'parts' => $createdParts,
        ], JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'backup_dir' => $baseName,
            'backup_path' => $backupDir,
            'total_parts' => count($createdParts),
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'total_size_gb' => round($totalSize / 1024 / 1024 / 1024, 2),
            'parts' => $createdParts,
            'manifest_path' => $manifestPath,
        ];
    }

    /**
     * Create split ZIP archives for a directory if it exceeds maxPartSize
     */
    protected function createSplitZipForDirectory(string $sourceDir, string $backupDir, string $basePartName, string $zipInternalPath): array
    {
        if (!is_dir($sourceDir)) {
            return [];
        }

        $parts = [];
        $currentPartNum = 1;
        $currentPartSize = 0;
        $currentZip = null;
        $currentZipPath = null;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $fileSize = $file->getSize();
            $relativePath = trim(str_replace($sourceDir, '', $file->getPathname()), '/');
            $zipPath = $zipInternalPath . '/' . $relativePath;

            // Check if we need to start a new ZIP
            if ($currentZip === null || $currentPartSize + $fileSize > $this->maxPartSize) {
                // Close previous ZIP if exists
                if ($currentZip !== null) {
                    $currentZip->close();
                    $actualSize = filesize($currentZipPath);
                    $parts[] = [
                        'filename' => basename($currentZipPath),
                        'path' => $currentZipPath,
                        'size' => $actualSize,
                        'size_mb' => round($actualSize / 1024 / 1024, 2),
                        'type' => str_contains($basePartName, 'public') ? 'storage_public' : 'storage_uploads',
                        'part_number' => $currentPartNum - 1,
                    ];
                }

                // Start new ZIP
                $currentZipPath = $backupDir . '/' . $basePartName . ($currentPartNum > 1 ? "_part{$currentPartNum}" : '') . '.zip';
                $currentZip = new \ZipArchive();
                if ($currentZip->open($currentZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    continue;
                }
                $currentPartNum++;
                $currentPartSize = 0;
            }

            // Add file to current ZIP
            $currentZip->addFile($file->getPathname(), $zipPath);
            $currentPartSize += $fileSize;
        }

        // Close last ZIP
        if ($currentZip !== null) {
            $currentZip->close();
            $actualSize = filesize($currentZipPath);
            $parts[] = [
                'filename' => basename($currentZipPath),
                'path' => $currentZipPath,
                'size' => $actualSize,
                'size_mb' => round($actualSize / 1024 / 1024, 2),
                'type' => str_contains($basePartName, 'public') ? 'storage_public' : 'storage_uploads',
                'part_number' => $currentPartNum - 1,
            ];
        }

        return $parts;
    }

    /**
     * Restore from split backup directory containing multiple ZIP parts
     */
    public function restoreFromSplitBackup(string $backupDirName): array
    {
        // Prevent timeout on shared hosting
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        $backupDir = storage_path('app/'.$this->baseDir.'/'.$backupDirName);
        if (!is_dir($backupDir)) {
            return ['success' => false, 'error' => 'Backup directory not found'];
        }

        // Read manifest
        $manifestPath = $backupDir . '/MANIFEST.json';
        if (!file_exists($manifestPath)) {
            return ['success' => false, 'error' => 'MANIFEST.json not found in backup directory'];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!$manifest) {
            return ['success' => false, 'error' => 'Invalid MANIFEST.json'];
        }

        $tempDir = storage_path('app/'.$this->baseDir.'/tmp_'.uniqid());
        @mkdir($tempDir, 0775, true);

        // Extract all parts
        foreach ($manifest['parts'] as $part) {
            $zipPath = $backupDir . '/' . $part['filename'];
            if (!file_exists($zipPath)) {
                $this->deleteDirectory($tempDir);
                return ['success' => false, 'error' => 'Missing part: ' . $part['filename']];
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                $this->deleteDirectory($tempDir);
                return ['success' => false, 'error' => 'Failed to open: ' . $part['filename']];
            }
            $zip->extractTo($tempDir);
            $zip->close();
        }

        // Restore storage files
        if (is_dir($tempDir.'/storage/public')) {
            $this->copyDirectory($tempDir.'/storage/public', storage_path('app/public'));
        }
        if (is_dir($tempDir.'/storage/uploads')) {
            $this->copyDirectory($tempDir.'/storage/uploads', storage_path('app/uploads'));
        }

        // Restore database
        $sqlPath = $tempDir.'/database/backup.sql';
        if (file_exists($sqlPath)) {
            $dbService = new DatabaseBackupService();
            $destDir = storage_path('app/database-backups');
            if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
            $destFile = $destDir.'/restored_'.date('Y-m-d_H-i-s').'.sql';
            @copy($sqlPath, $destFile);
            $dbService->restoreBackup(basename($destFile));
        }

        // Cleanup temp
        $this->deleteDirectory($tempDir);

        return ['success' => true, 'parts_restored' => count($manifest['parts'])];
    }

    public function restoreFromZip(string $zipPath): array
    {
        // Prevent timeout on shared hosting
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        if (!file_exists($zipPath)) {
            return ['success' => false, 'error' => 'Archive file not found'];
        }

        $ext = strtolower(pathinfo($zipPath, PATHINFO_EXTENSION));
        $tempDir = storage_path('app/'.$this->baseDir.'/tmp_'.uniqid());
        @mkdir($tempDir, 0775, true);

        if ($ext === 'zip' && class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                return ['success' => false, 'error' => 'Failed to open ZIP'];
            }
            $zip->extractTo($tempDir);
            $zip->close();
        } elseif ($ext === 'tar' && class_exists('PharData')) {
            $phar = new \PharData($zipPath);
            $phar->extractTo($tempDir, null, true);
        } else {
            return ['success' => false, 'error' => 'Unsupported archive type or missing extensions'];
        }

        // Restore storage files
        $this->copyDirectory($tempDir.'/storage/public', storage_path('app/public'));
        $this->copyDirectory($tempDir.'/storage/uploads', storage_path('app/uploads'));

        // Restore database
        $sqlPath = $tempDir.'/database/backup.sql';
        $dbService = new DatabaseBackupService();
        if (file_exists($sqlPath)) {
            $destDir = storage_path('app/database-backups');
            if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
            $destFile = $destDir.'/restored_'.date('Y-m-d_H-i-s').'.sql';
            @copy($sqlPath, $destFile);
            $dbService->restoreBackup(basename($destFile));
        }

        // Cleanup temp
        $this->deleteDirectory($tempDir);

        return ['success' => true];
    }

    /**
     * List available split backups
     */
    public function listSplitBackups(): array
    {
        $backups = [];
        $backupRoot = storage_path('app/'.$this->baseDir);

        if (!is_dir($backupRoot)) {
            return $backups;
        }

        $directories = array_filter(glob($backupRoot.'/*'), 'is_dir');

        foreach ($directories as $dir) {
            $manifestPath = $dir . '/MANIFEST.json';
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if ($manifest) {
                    $backups[] = [
                        'name' => basename($dir),
                        'path' => $dir,
                        'created_at' => $manifest['created_at'] ?? null,
                        'total_parts' => $manifest['total_parts'] ?? 0,
                        'total_size_gb' => $manifest['total_size_gb'] ?? 0,
                        'manifest' => $manifest,
                    ];
                }
            }
        }

        // Sort by created_at descending
        usort($backups, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return $backups;
    }

    protected function addDirectoryToZip($zip, string $sourceDir, string $zipBase): void
    {
        if (!is_dir($sourceDir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $localPath = $zipBase.'/'.trim(str_replace($sourceDir, '', $file->getPathname()), '/');
            if ($file->isDir()) {
                if (method_exists($zip, 'addEmptyDir')) {
                    $zip->addEmptyDir($localPath);
                }
            } else {
                $zip->addFile($file->getPathname(), $localPath);
            }
        }
    }

    protected function addDirectoryToPhar(\PharData $phar, string $sourceDir, string $base): void
    {
        if (!is_dir($sourceDir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $localPath = $base.'/'.trim(str_replace($sourceDir, '', $file->getPathname()), '/');
            if ($file->isDir()) {
                $phar->addEmptyDir($localPath);
            } else {
                $phar->addFile($file->getPathname(), $localPath);
            }
        }
    }

    protected function copyDirectory(string $from, string $to): void
    {
        if (!is_dir($from)) return;
        @mkdir($to, 0775, true);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $destPath = $to.'/'.trim(str_replace($from, '', $item->getPathname()), '/');
            if ($item->isDir()) {
                @mkdir($destPath, 0775, true);
            } else {
                @copy($item->getPathname(), $destPath);
            }
        }
    }

    protected function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }
        @rmdir($path);
    }

    protected function copyIntoDir(string $src, string $dest): void
    {
        $dir = dirname($dest);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @copy($src, $dest);
    }
}
