<?php

namespace App\Console\Commands;

use App\Services\DataQualityService;
use App\Models\Book;
use App\Models\DataQualityIssue;
use Illuminate\Console\Command;

class VerifyDataQuality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:verify-quality
                            {--book-id= : Check specific book by ID}
                            {--csv-import-id= : Check books from specific CSV import}
                            {--clear-existing : Clear existing unresolved issues before checking}
                            {--show-issues : Display all found issues}
                            {--severity= : Filter issues by severity (critical, warning, info)}
                            {--type= : Filter issues by type}
                            {--resolve= : Mark all issues of a specific type as resolved}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify data quality of books and flag suspicious records';

    /**
     * Execute the console command.
     */
    public function handle(DataQualityService $qualityService): int
    {
        $this->info('Running Data Quality Checks...');
        $this->newLine();

        // Handle resolve option first
        if ($resolveType = $this->option('resolve')) {
            return $this->resolveIssues($resolveType);
        }

        try {
            // Determine which books to check
            $books = $this->getBooksToCheck();

            if ($books->isEmpty()) {
                $this->warn('No books found to check.');
                return self::FAILURE;
            }

            $this->info("Checking {$books->count()} book(s)...");

            // Run quality checks
            $report = $qualityService->runQualityChecks(
                $books,
                $this->option('csv-import-id'),
                $this->option('clear-existing')
            );

            // Display report summary
            $this->displayReport($report);

            // Display issues if requested
            if ($this->option('show-issues')) {
                $this->displayIssues();
            }

            // Return success or failure based on critical issues
            if ($report['critical_issues'] > 0) {
                $this->newLine();
                $this->error('⚠ Critical issues found! Please review and resolve them.');
                return self::FAILURE;
            }

            $this->newLine();
            $this->info('✓ Data quality check completed successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during data quality check: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Get books to check based on options
     */
    protected function getBooksToCheck()
    {
        if ($bookId = $this->option('book-id')) {
            return Book::with([
                'languages',
                'classifications',
                'creators',
                'files',
                'publisher',
                'collection',
            ])->where('id', $bookId)->get();
        }

        if ($csvImportId = $this->option('csv-import-id')) {
            // Get books created/updated in this import
            // Note: We need to track this in csv_imports table or use timestamps
            $this->warn('CSV import filtering not yet implemented. Checking all books.');
        }

        // Check all books
        return Book::with([
            'languages',
            'classifications',
            'creators',
            'files',
            'publisher',
            'collection',
        ])->get();
    }

    /**
     * Display report summary
     */
    protected function displayReport(array $report): void
    {
        $this->newLine();
        $this->info('=== Data Quality Report ===');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Checked At', $report['checked_at']],
                ['Total Books Checked', $report['total_books_checked']],
                ['Total Issues Found', $report['total_issues_found']],
                ['Critical Issues', "<fg=red>{$report['critical_issues']}</>"],
                ['Warnings', "<fg=yellow>{$report['warnings']}</>"],
                ['Info Issues', "<fg=blue>{$report['info_issues']}</>"],
            ]
        );

        if (!empty($report['issues_by_type'])) {
            $this->newLine();
            $this->info('Issues by Type:');

            $rows = [];
            foreach ($report['issues_by_type'] as $type => $count) {
                $rows[] = [$type, $count];
            }

            $this->table(['Issue Type', 'Count'], $rows);
        }
    }

    /**
     * Display all found issues
     */
    protected function displayIssues(): void
    {
        $this->newLine();
        $this->info('=== Issues Details ===');
        $this->newLine();

        $query = DataQualityIssue::with('book')->unresolved();

        // Apply filters
        if ($severity = $this->option('severity')) {
            $query->where('severity', $severity);
        }

        if ($type = $this->option('type')) {
            $query->where('issue_type', $type);
        }

        $issues = $query->orderBy('severity')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        if ($issues->isEmpty()) {
            $this->warn('No issues found matching your criteria.');
            return;
        }

        $rows = [];
        foreach ($issues as $issue) {
            $bookTitle = $issue->book ? substr($issue->book->title, 0, 40) : 'N/A';
            $severity = match ($issue->severity) {
                'critical' => "<fg=red>{$issue->severity}</>",
                'warning' => "<fg=yellow>{$issue->severity}</>",
                'info' => "<fg=blue>{$issue->severity}</>",
                default => $issue->severity,
            };

            $rows[] = [
                $issue->id,
                $issue->book_id,
                $bookTitle,
                $severity,
                $issue->issue_type,
                substr($issue->message, 0, 60),
            ];
        }

        $this->table(
            ['ID', 'Book ID', 'Book Title', 'Severity', 'Type', 'Message'],
            $rows
        );

        if ($issues->count() >= 50) {
            $this->warn('Showing first 50 issues. Use filters to narrow down results.');
        }
    }

    /**
     * Resolve issues of a specific type
     */
    protected function resolveIssues(string $type): int
    {
        $this->info("Marking all '{$type}' issues as resolved...");

        $count = DataQualityIssue::where('issue_type', $type)
            ->where('is_resolved', false)
            ->update([
                'is_resolved' => true,
                'resolved_at' => now(),
                'resolved_by' => 1, // System user
                'resolution_notes' => "Bulk resolved via command line on " . now()->toDateTimeString(),
            ]);

        $this->info("Resolved {$count} issue(s) of type '{$type}'.");

        return self::SUCCESS;
    }
}
