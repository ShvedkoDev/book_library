#!/usr/bin/env php
<?php

/**
 * Script to check for books without covers and orphaned PNG files
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Support\Facades\Storage;

echo "========================================\n";
echo "BOOK COVER ANALYSIS\n";
echo "========================================\n\n";

// 1. Find books without covers
echo "1. BOOKS WITHOUT COVERS\n";
echo "----------------------------------------\n";

$booksWithoutCovers = Book::whereDoesntHave('primaryThumbnail')->get();

echo "Total books without covers: " . $booksWithoutCovers->count() . "\n\n";

if ($booksWithoutCovers->count() > 0) {
    echo "Book ID | PALM Code | Title\n";
    echo "--------|-----------|------\n";
    foreach ($booksWithoutCovers as $book) {
        $title = substr($book->title, 0, 60);
        if (strlen($book->title) > 60) {
            $title .= '...';
        }
        printf("%-7s | %-9s | %s\n",
            $book->id,
            $book->palm_code ?? 'N/A',
            $title
        );
    }

    // Export to CSV
    $csvFile = storage_path('app/books-without-covers.csv');
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, ['Book ID', 'PALM Code', 'Title', 'Collection', 'Language']);
    foreach ($booksWithoutCovers as $book) {
        fputcsv($fp, [
            $book->id,
            $book->palm_code ?? '',
            $book->title,
            $book->collection?->name ?? '',
            $book->primaryLanguage()?->name ?? ''
        ]);
    }
    fclose($fp);
    echo "\nExported to: $csvFile\n";
}

echo "\n\n";

// 2. Find orphaned PNG files
echo "2. ORPHANED PNG FILES (not linked to any book)\n";
echo "----------------------------------------\n";

// Get all PNG files from storage
$storagePath = storage_path('app/public/books');
$allPngFiles = glob($storagePath . '/*.png');

echo "Total PNG files in storage: " . count($allPngFiles) . "\n";

// Get all thumbnail file paths from database
$usedFiles = BookFile::where('file_type', 'thumbnail')
    ->whereNotNull('file_path')
    ->pluck('file_path')
    ->map(function($path) {
        // Normalize paths - remove 'books/' prefix if exists
        return basename($path);
    })
    ->toArray();

echo "PNG files referenced in database: " . count($usedFiles) . "\n\n";

// Find orphaned files
$orphanedFiles = [];
foreach ($allPngFiles as $filePath) {
    $fileName = basename($filePath);

    if (!in_array($fileName, $usedFiles)) {
        $orphanedFiles[] = [
            'name' => $fileName,
            'size' => filesize($filePath),
            'path' => $filePath
        ];
    }
}

echo "Orphaned PNG files: " . count($orphanedFiles) . "\n\n";

if (count($orphanedFiles) > 0) {
    echo "Filename | Size\n";
    echo "---------|------\n";
    foreach (array_slice($orphanedFiles, 0, 50) as $file) {
        $size = round($file['size'] / 1024, 1) . ' KB';
        $name = substr($file['name'], 0, 70);
        if (strlen($file['name']) > 70) {
            $name .= '...';
        }
        printf("%-70s | %s\n", $name, $size);
    }

    if (count($orphanedFiles) > 50) {
        echo "\n... and " . (count($orphanedFiles) - 50) . " more files\n";
    }

    // Export to CSV
    $csvFile = storage_path('app/orphaned-png-files.csv');
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, ['Filename', 'Size (bytes)', 'Path']);
    foreach ($orphanedFiles as $file) {
        fputcsv($fp, [
            $file['name'],
            $file['size'],
            $file['path']
        ]);
    }
    fclose($fp);
    echo "\nExported to: $csvFile\n";
}

echo "\n\n";

// 3. Summary
echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "Total books in database: " . Book::count() . "\n";
echo "Books with covers: " . Book::whereHas('primaryThumbnail')->count() . "\n";
echo "Books without covers: " . $booksWithoutCovers->count() . "\n";
echo "Total PNG files in storage: " . count($allPngFiles) . "\n";
echo "PNG files in use: " . count($usedFiles) . "\n";
echo "Orphaned PNG files: " . count($orphanedFiles) . "\n";
echo "Disk space used by orphaned files: " . round(array_sum(array_column($orphanedFiles, 'size')) / 1024 / 1024, 2) . " MB\n";
echo "========================================\n\n";

echo "CSV exports saved to:\n";
echo "  - storage/app/books-without-covers.csv\n";
echo "  - storage/app/orphaned-png-files.csv\n\n";
