<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\BookRelationship;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTranslationRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:generate-translations
                            {--dry-run : Preview relationships without creating them}
                            {--clear : Clear existing translation relationships before generating new ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate translation relationships between books based on identical translated titles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=====================================');
        $this->info('GENERATING TRANSLATION RELATIONSHIPS');
        $this->info('=====================================');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $clear = $this->option('clear');

        if ($clear && !$dryRun) {
            if ($this->confirm('This will delete all existing translation relationships. Continue?')) {
                $deleted = BookRelationship::where('relationship_type', 'translated')->delete();
                $this->info("Deleted {$deleted} existing translation relationships");
                $this->newLine();
            } else {
                $this->info('Cancelled.');
                return 0;
            }
        }

        // Find books with translated titles
        $booksWithTranslations = Book::where('is_active', true)
            ->whereNotNull('translated_title')
            ->where('translated_title', '!=', '')
            ->with('languages')
            ->get();

        $this->info("Found {$booksWithTranslations->count()} books with translated titles");
        $this->newLine();

        // Group books by translated title (case-insensitive, trimmed)
        $translationGroups = $booksWithTranslations->groupBy(function ($book) {
            return strtolower(trim($book->translated_title));
        })->filter(function ($group) {
            // Only keep groups with 2 or more books (translations must have multiple versions)
            return $group->count() >= 2;
        });

        $this->info("Found {$translationGroups->count()} translation groups");
        $this->newLine();

        if ($translationGroups->isEmpty()) {
            $this->warn('No translation groups found. Books need identical translated_title values to be linked.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($translationGroups->count());
        $progressBar->start();

        $stats = [
            'groups_processed' => 0,
            'relationships_created' => 0,
            'skipped_same_language' => 0,
        ];

        foreach ($translationGroups as $translatedTitle => $books) {
            $stats['groups_processed']++;

            // Create bidirectional relationships between all books in the group
            foreach ($books as $book1) {
                foreach ($books as $book2) {
                    if ($book1->id === $book2->id) {
                        continue; // Skip self-relationship
                    }

                    // Check if they have different languages (optional but recommended)
                    $book1Languages = $book1->languages->pluck('code')->toArray();
                    $book2Languages = $book2->languages->pluck('code')->toArray();

                    // If both books have the same language(s), they might be duplicates, not translations
                    $hasCommonLanguage = !empty(array_intersect($book1Languages, $book2Languages));
                    if ($hasCommonLanguage && count($book1Languages) === 1 && count($book2Languages) === 1) {
                        $stats['skipped_same_language']++;
                        continue;
                    }

                    if (!$dryRun) {
                        // Check if relationship already exists
                        $exists = BookRelationship::where('book_id', $book1->id)
                            ->where('related_book_id', $book2->id)
                            ->where('relationship_type', 'translated')
                            ->exists();

                        if (!$exists) {
                            BookRelationship::create([
                                'book_id' => $book1->id,
                                'related_book_id' => $book2->id,
                                'relationship_type' => 'translated',
                                'notes' => 'Auto-generated: Identical translated title',
                            ]);
                            $stats['relationships_created']++;
                        }
                    } else {
                        $stats['relationships_created']++;
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Translation Relationship Generation Complete!');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Translation groups found', $stats['groups_processed']],
                ['Relationships ' . ($dryRun ? 'would be created' : 'created'), $stats['relationships_created']],
                ['Skipped (same language)', $stats['skipped_same_language']],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN MODE: No relationships were actually created.');
            $this->info('Run without --dry-run to create relationships.');
        }

        $this->newLine();

        // Show example groups
        if ($translationGroups->count() > 0) {
            $this->info('Example translation groups:');
            $this->newLine();

            foreach ($translationGroups->take(3) as $translatedTitle => $books) {
                $this->line("Translated title: <fg=cyan>{$translatedTitle}</>");
                foreach ($books as $book) {
                    $languages = $book->languages->pluck('name')->join(', ');
                    $this->line("  - [{$languages}] {$book->title} (ID: {$book->id})");
                    $this->line("    <fg=gray>http://localhost/library/{$book->slug}</>");
                }
                $this->newLine();
            }
        }

        return 0;
    }
}
