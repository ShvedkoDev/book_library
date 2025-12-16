<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\ThumbnailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:regenerate-thumbnails {--force : Force regeneration even for custom covers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate placeholder thumbnails for books using the updated color palette';

    /**
     * Execute the console command.
     */
    public function handle(ThumbnailService $thumbnailService)
    {
        $this->info('========================================');
        $this->info('REGENERATING BOOK THUMBNAILS');
        $this->info('========================================');

        $books = Book::all();
        $total = $books->count();
        $force = $this->option('force');

        $this->info("Found {$total} books. Starting regeneration...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $regenerated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($books as $book) {
            // Skip if book has a custom uploaded thumbnail (not a generated placeholder)
            // We assume generated placeholders are SVGs in thumbnails/placeholders/
            $thumbnail = $book->primaryThumbnail;
            
            $shouldRegenerate = true;
            
            if ($thumbnail && !$force) {
                // If it's not a placeholder SVG, skip it (preserve custom covers)
                if (!str_contains($thumbnail->file_path, 'thumbnails/placeholders/') && 
                    !str_ends_with($thumbnail->file_path, '.svg')) {
                    $shouldRegenerate = false;
                    $skipped++;
                }
            }
            
            if ($shouldRegenerate) {
                try {
                    // Generate new placeholder
                    $url = $thumbnailService->savePlaceholderToFile($book);
                    
                    if ($url) {
                        $filename = 'thumbnails/placeholders/' . Str::slug($book->title) . '_' . $book->id . '.svg';
                        
                        // Check if record exists
                        $bookFile = $book->files()->where('file_type', 'thumbnail')->first();
                        
                        if (!$bookFile) {
                            $book->files()->create([
                                'file_type' => 'thumbnail',
                                'file_path' => $filename,
                                'filename' => basename($filename),
                                'file_size' => Storage::disk('public')->size($filename),
                                'mime_type' => 'image/svg+xml',
                                'disk' => 'public',
                                'is_primary' => true,
                            ]);
                        } else {
                            // Update existing record
                            $bookFile->update([
                                'file_path' => $filename,
                                'filename' => basename($filename),
                                'file_size' => Storage::disk('public')->size($filename),
                                'mime_type' => 'image/svg+xml',
                            ]);
                        }
                        $regenerated++;
                    } else {
                        $failed++;
                        $this->error("\nFailed to generate for '{$book->title}'");
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("\nError for '{$book->title}': {$e->getMessage()}");
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Done!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Regenerated', $regenerated],
                ['Skipped (Custom Covers)', $skipped],
                ['Failed', $failed],
            ]
        );
    }
}
