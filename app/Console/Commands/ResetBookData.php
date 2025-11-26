<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetBookData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:reset
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all library data (books, collections, publishers, etc.) but preserve users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  This will DELETE ALL library data (books, collections, publishers, creators, etc.). Users and profiles will be preserved. Are you sure?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('🗑️  Deleting all library data...');

        try {
            // Disable foreign key checks temporarily
            // Note: TRUNCATE implicitly commits, so we don't use transactions
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Order matters due to relationships
            $tables = [
                // Analytics
                'book_views',
                'book_downloads',
                'search_queries',
                'filter_analytics',

                // User interactions
                'book_ratings',
                'book_reviews',
                'book_bookmarks',

                // Book relationships
                'book_identifiers',
                'book_keywords',
                'book_files',
                'book_relationships',
                'library_references',
                'book_classifications',
                'book_creators',
                'book_languages',
                'book_locations',

                // Main book table
                'books',

                // CSV imports and quality issues
                'data_quality_issues',
                'csv_imports',

                // Lookup tables (collections, publishers, creators)
                'collections',
                'publishers',
                'creators',

                // Classification system (HARD RESET - includes everything)
                'classification_values',
                'classification_types',

                // Languages and locations
                'languages',
                'geographic_locations',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $this->line("   ✓ Truncated {$table} ({$count} records)");
                } else {
                    $this->warn("   ⚠ Table {$table} does not exist, skipping...");
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->newLine();
            $this->info('✅ All library data has been HARD RESET successfully!');
            $this->newLine();
            $this->info('📝 Next steps:');
            $this->line('   Option 1 (Recommended): Import CSV directly - it will auto-create everything');
            $this->line('   Option 2: Run db:seed first, then import CSV');
            $this->newLine();
            $this->comment('💡 CSV Import Auto-Creates:');
            $this->comment('   - Classification types & values');
            $this->comment('   - Languages');
            $this->comment('   - Geographic locations');
            $this->comment('   - Collections, Publishers, Creators');
            $this->newLine();

            return self::SUCCESS;

        } catch (\Exception $e) {
            // Re-enable foreign key checks on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->error('❌ Error deleting book data: ' . $e->getMessage());

            // Only show stack trace in verbose mode
            if ($this->output->isVerbose()) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
