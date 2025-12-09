<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DataQualityIssue;
use App\Models\CsvImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DataQualityService
{
    /**
     * Run all data quality checks on books
     *
     * @param Collection|null $books Books to check (null = check all books)
     * @param int|null $csvImportId Associated CSV import ID
     * @param bool $clearExisting Clear existing issues before checking
     * @return array Report summary
     */
    public function runQualityChecks(
        ?Collection $books = null,
        ?int $csvImportId = null,
        bool $clearExisting = false
    ): array {
        $report = [
            'total_books_checked' => 0,
            'total_issues_found' => 0,
            'critical_issues' => 0,
            'warnings' => 0,
            'info_issues' => 0,
            'issues_by_type' => [],
            'checked_at' => now()->toDateTimeString(),
        ];

        try {
            // Get books to check
            if ($books === null) {
                $books = Book::with([
                    'languages',
                    'bookClassifications.classificationValue',
                    'creators',
                    'files',
                    'publisher',
                    'collection',
                ])->get();
            }

            // Clear existing unresolved issues if requested
            if ($clearExisting && $csvImportId) {
                DataQualityIssue::where('csv_import_id', $csvImportId)
                    ->where('is_resolved', false)
                    ->delete();
            }

            $report['total_books_checked'] = $books->count();

            foreach ($books as $book) {
                $issues = $this->checkBook($book, $csvImportId);

                foreach ($issues as $issue) {
                    $report['total_issues_found']++;

                    // Count by severity
                    match ($issue->severity) {
                        'critical' => $report['critical_issues']++,
                        'warning' => $report['warnings']++,
                        'info' => $report['info_issues']++,
                        default => null,
                    };

                    // Count by type
                    if (!isset($report['issues_by_type'][$issue->issue_type])) {
                        $report['issues_by_type'][$issue->issue_type] = 0;
                    }
                    $report['issues_by_type'][$issue->issue_type]++;
                }
            }

            // Sort issues by type by count (descending)
            arsort($report['issues_by_type']);

        } catch (\Exception $e) {
            Log::error('Data Quality Check Error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Check a single book for data quality issues
     *
     * @param Book $book
     * @param int|null $csvImportId
     * @return Collection<DataQualityIssue>
     */
    public function checkBook(Book $book, ?int $csvImportId = null): Collection
    {
        $issues = collect();

        // 1. Check for missing title (critical)
        $issues = $issues->merge($this->checkTitle($book, $csvImportId));

        // 2. Check for valid access_level (critical)
        $issues = $issues->merge($this->checkAccessLevel($book, $csvImportId));

        // 3. Check for missing description (warning)
        $issues = $issues->merge($this->checkDescription($book, $csvImportId));

        // 4. Check for missing languages (warning)
        $issues = $issues->merge($this->checkLanguages($book, $csvImportId));

        // 5. Check for missing classifications (warning)
        $issues = $issues->merge($this->checkClassifications($book, $csvImportId));

        // 6. Check for missing files (warning)
        $issues = $issues->merge($this->checkFiles($book, $csvImportId));

        // 7. Check for relationship integrity (info)
        $issues = $issues->merge($this->checkRelationships($book, $csvImportId));

        // 8. Check for missing metadata (info)
        $issues = $issues->merge($this->checkMetadata($book, $csvImportId));

        return $issues;
    }

    /**
     * Check if book has a valid title
     */
    protected function checkTitle(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        if (empty($book->title) || trim($book->title) === '') {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_title',
                'severity' => 'critical',
                'field_name' => 'title',
                'message' => 'Book has no title or empty title',
                'context' => [
                    'internal_id' => $book->internal_id,
                    'palm_code' => $book->palm_code,
                ],
            ]));
        } elseif (strlen(trim($book->title)) < 3) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'short_title',
                'severity' => 'warning',
                'field_name' => 'title',
                'message' => "Book title is unusually short (less than 3 characters): '{$book->title}'",
                'context' => [
                    'title_length' => strlen(trim($book->title)),
                    'title' => $book->title,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Check if book has a valid access level
     */
    protected function checkAccessLevel(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        $validLevels = ['full', 'limited', 'unavailable'];

        if (empty($book->access_level) || !in_array($book->access_level, $validLevels)) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'invalid_access_level',
                'severity' => 'critical',
                'field_name' => 'access_level',
                'message' => "Book has invalid access level: '{$book->access_level}'",
                'context' => [
                    'current_value' => $book->access_level,
                    'valid_values' => $validLevels,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Check if book has a description
     */
    protected function checkDescription(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        if (empty($book->description) || trim($book->description) === '') {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_description',
                'severity' => 'warning',
                'field_name' => 'description',
                'message' => 'Book has no description',
                'context' => [
                    'title' => $book->title,
                ],
            ]));
        } elseif (strlen(trim($book->description)) < 20) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'short_description',
                'severity' => 'info',
                'field_name' => 'description',
                'message' => 'Book description is very short (less than 20 characters)',
                'context' => [
                    'description_length' => strlen(trim($book->description)),
                    'title' => $book->title,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Check if book has languages assigned
     */
    protected function checkLanguages(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        $languageCount = $book->languages()->count();

        if ($languageCount === 0) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_languages',
                'severity' => 'warning',
                'field_name' => 'languages',
                'message' => 'Book has no languages assigned',
                'context' => [
                    'title' => $book->title,
                    'expected_count' => '1 or more',
                    'actual_count' => 0,
                ],
            ]));
        } else {
            // Check if there's a primary language
            $hasPrimary = $book->languages()->wherePivot('is_primary', true)->exists();

            if (!$hasPrimary) {
                $issues->push(DataQualityIssue::create([
                    'book_id' => $book->id,
                    'csv_import_id' => $csvImportId,
                    'issue_type' => 'no_primary_language',
                    'severity' => 'info',
                    'field_name' => 'languages',
                    'message' => 'Book has languages but none marked as primary',
                    'context' => [
                        'title' => $book->title,
                        'language_count' => $languageCount,
                    ],
                ]));
            }
        }

        return $issues;
    }

    /**
     * Check if book has classifications assigned
     */
    protected function checkClassifications(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        // Count all classification types
        $classificationCount = $book->purposeClassifications()->count() +
                             $book->genreClassifications()->count() +
                             $book->subgenreClassifications()->count() +
                             $book->learnerLevelClassifications()->count();

        if ($classificationCount === 0) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_classifications',
                'severity' => 'warning',
                'field_name' => 'classifications',
                'message' => 'Book has no classifications assigned',
                'context' => [
                    'title' => $book->title,
                    'expected_count' => '1 or more',
                    'actual_count' => 0,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Check if book has files associated
     */
    protected function checkFiles(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        $fileCount = $book->files()->count();

        if ($fileCount === 0) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_files',
                'severity' => 'warning',
                'field_name' => 'files',
                'message' => 'Book has no files associated (PDFs, thumbnails, etc.)',
                'context' => [
                    'title' => $book->title,
                    'access_level' => $book->access_level,
                ],
            ]));
        } else {
            // Check if there's a primary PDF for full access books
            if ($book->access_level === 'full') {
                $hasPrimaryPdf = $book->files()
                    ->where('file_type', 'pdf')
                    ->where('is_primary', true)
                    ->exists();

                if (!$hasPrimaryPdf) {
                    $issues->push(DataQualityIssue::create([
                        'book_id' => $book->id,
                        'csv_import_id' => $csvImportId,
                        'issue_type' => 'missing_primary_pdf',
                        'severity' => 'warning',
                        'field_name' => 'files',
                        'message' => 'Book has full access but no primary PDF file',
                        'context' => [
                            'title' => $book->title,
                            'access_level' => $book->access_level,
                            'file_count' => $fileCount,
                        ],
                    ]));
                }
            }

            // Check if there's a thumbnail
            $hasThumbnail = $book->files()->where('file_type', 'thumbnail')->exists();

            if (!$hasThumbnail) {
                $issues->push(DataQualityIssue::create([
                    'book_id' => $book->id,
                    'csv_import_id' => $csvImportId,
                    'issue_type' => 'missing_thumbnail',
                    'severity' => 'info',
                    'field_name' => 'files',
                    'message' => 'Book has no thumbnail image',
                    'context' => [
                        'title' => $book->title,
                    ],
                ]));
            }
        }

        return $issues;
    }

    /**
     * Check relationship integrity
     */
    protected function checkRelationships(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        // Check if collection exists
        if ($book->collection_id && !$book->collection) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'broken_collection_reference',
                'severity' => 'critical',
                'field_name' => 'collection_id',
                'message' => 'Book references non-existent collection',
                'context' => [
                    'title' => $book->title,
                    'collection_id' => $book->collection_id,
                ],
            ]));
        }

        // Check if publisher exists
        if ($book->publisher_id && !$book->publisher) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'broken_publisher_reference',
                'severity' => 'critical',
                'field_name' => 'publisher_id',
                'message' => 'Book references non-existent publisher',
                'context' => [
                    'title' => $book->title,
                    'publisher_id' => $book->publisher_id,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Check for missing metadata
     */
    protected function checkMetadata(Book $book, ?int $csvImportId): Collection
    {
        $issues = collect();

        // Check for missing publication year
        if (empty($book->publication_year)) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_publication_year',
                'severity' => 'info',
                'field_name' => 'publication_year',
                'message' => 'Book has no publication year',
                'context' => [
                    'title' => $book->title,
                ],
            ]));
        }

        // Check for missing pages
        if (empty($book->pages)) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_pages',
                'severity' => 'info',
                'field_name' => 'pages',
                'message' => 'Book has no page count',
                'context' => [
                    'title' => $book->title,
                ],
            ]));
        }

        // Check for missing creators (authors, illustrators, etc.)
        $creatorCount = $book->creators()->count();

        if ($creatorCount === 0) {
            $issues->push(DataQualityIssue::create([
                'book_id' => $book->id,
                'csv_import_id' => $csvImportId,
                'issue_type' => 'missing_creators',
                'severity' => 'info',
                'field_name' => 'creators',
                'message' => 'Book has no creators (authors, illustrators, etc.)',
                'context' => [
                    'title' => $book->title,
                ],
            ]));
        }

        return $issues;
    }

    /**
     * Generate a human-readable report summary
     */
    public function generateReportSummary(array $report): string
    {
        $summary = "Data Quality Report\n";
        $summary .= "==================\n\n";
        $summary .= "Checked: {$report['checked_at']}\n";
        $summary .= "Total Books Checked: {$report['total_books_checked']}\n";
        $summary .= "Total Issues Found: {$report['total_issues_found']}\n\n";

        $summary .= "Issues by Severity:\n";
        $summary .= "  Critical: {$report['critical_issues']}\n";
        $summary .= "  Warnings: {$report['warnings']}\n";
        $summary .= "  Info: {$report['info_issues']}\n\n";

        if (!empty($report['issues_by_type'])) {
            $summary .= "Issues by Type:\n";
            foreach ($report['issues_by_type'] as $type => $count) {
                $summary .= "  {$type}: {$count}\n";
            }
        }

        if (isset($report['error'])) {
            $summary .= "\nError: {$report['error']}\n";
        }

        return $summary;
    }

    /**
     * Get unresolved issues summary
     */
    public function getUnresolvedIssuesSummary(): array
    {
        $issues = DataQualityIssue::unresolved()->get();

        return [
            'total' => $issues->count(),
            'critical' => $issues->where('severity', 'critical')->count(),
            'warnings' => $issues->where('severity', 'warning')->count(),
            'info' => $issues->where('severity', 'info')->count(),
            'by_type' => $issues->groupBy('issue_type')->map->count()->toArray(),
        ];
    }
}
