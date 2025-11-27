<?php

namespace App\Console\Commands;

use App\Models\BookFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixBookFilePaths extends Command
{
    protected $signature = 'books:fix-file-paths {--dry-run : Show what would be done without making changes}';
    protected $description = 'Fix book file paths to match actual file locations';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Checking book file paths...');

        $bookFiles = BookFile::all();
        $fixedCount = 0;
        $skippedCount = 0;
        $missingCount = 0;

        foreach ($bookFiles as $bookFile) {
            $currentPath = $bookFile->file_path;

            // Skip if no file path
            if (empty($currentPath)) {
                continue;
            }

            // Check if current path exists
            if (Storage::disk('public')->exists($currentPath)) {
                $this->line("✓ File exists: {$currentPath}");
                $skippedCount++;
                continue;
            }

            // Try to find the file in the books directory
            $filename = $bookFile->filename;
            $possiblePath = "books/{$filename}";

            // Try exact match first
            if (Storage::disk('public')->exists($possiblePath)) {
                $this->info("  → Found at: {$possiblePath}");

                if (!$dryRun) {
                    $bookFile->update(['file_path' => $possiblePath]);
                    $this->info("  ✓ Updated path for: {$filename}");
                }

                $fixedCount++;
            } else {
                // Try case-insensitive search
                $allFiles = Storage::disk('public')->files('books');
                $found = false;

                foreach ($allFiles as $file) {
                    if (strcasecmp(basename($file), $filename) === 0) {
                        $this->info("  → Found at: {$file} (case difference)");

                        if (!$dryRun) {
                            $bookFile->update(['file_path' => $file]);
                            $this->info("  ✓ Updated path for: {$filename}");
                        }

                        $fixedCount++;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $this->error("  ✗ File not found: {$filename}");
                    $this->error("    Current path: {$currentPath}");
                    $this->error("    Tried: {$possiblePath}");
                    $missingCount++;
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Files already correct: {$skippedCount}");
        $this->info("  Files fixed: {$fixedCount}");
        $this->error("  Files missing: {$missingCount}");

        if ($dryRun && $fixedCount > 0) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
