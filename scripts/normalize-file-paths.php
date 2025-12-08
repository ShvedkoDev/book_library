#!/usr/bin/env php
<?php

/**
 * Normalize Unicode file paths in database to NFC form
 * Fixes issue where NFD (decomposed) Unicode in database doesn't match NFC (composed) Unicode in filesystem
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BookFile;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "UNICODE PATH NORMALIZATION\n";
echo "========================================\n\n";

// Get all book files
$files = BookFile::whereNotNull('file_path')->get();

echo "Total files to check: " . $files->count() . "\n\n";

$updated = 0;
$unchanged = 0;
$errors = 0;

foreach ($files as $file) {
    $originalPath = $file->file_path;

    // Skip if empty
    if (empty($originalPath)) {
        continue;
    }

    // Normalize to NFC (composed form)
    $normalizedPath = Normalizer::normalize($originalPath, Normalizer::NFC);

    // Check if different
    if ($originalPath !== $normalizedPath) {
        try {
            // Update the path
            DB::table('book_files')
                ->where('id', $file->id)
                ->update(['file_path' => $normalizedPath]);

            $updated++;

            // Show first 10 updates
            if ($updated <= 10) {
                echo "UPDATED [ID: {$file->id}]:\n";
                echo "  Before: {$originalPath}\n";
                echo "  After:  {$normalizedPath}\n";
                echo "  Book:   " . ($file->book ? $file->book->title : 'N/A') . "\n\n";
            }
        } catch (\Exception $e) {
            $errors++;
            echo "ERROR updating file ID {$file->id}: " . $e->getMessage() . "\n";
        }
    } else {
        $unchanged++;
    }
}

echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "Total files processed: " . $files->count() . "\n";
echo "Files updated: {$updated}\n";
echo "Files unchanged: {$unchanged}\n";
echo "Errors: {$errors}\n";
echo "========================================\n\n";

if ($updated > 10) {
    echo "Note: Only first 10 updates shown above.\n";
    echo "All {$updated} files have been updated in the database.\n\n";
}

echo "Done! You may need to clear cache:\n";
echo "  php artisan cache:clear\n";
echo "  php artisan view:clear\n\n";
