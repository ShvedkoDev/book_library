<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AppBackupService
{
    protected string $disk = 'local';
    protected string $baseDir = 'full-backups';

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
