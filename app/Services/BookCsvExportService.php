<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class BookCsvExportService
{
    protected array $config;
    protected array $fieldMapping;
    protected string $separator;
    protected array $headers = [];

    public function __construct()
    {
        $this->config = config('csv-import');
        $this->fieldMapping = $this->config['field_mapping'];
        $this->separator = $this->config['separator'];
    }

    /**
     * Export all books to CSV
     *
     * @param array $options
     * @return string File path of exported CSV
     */
    public function exportAll(array $options = []): string
    {
        $query = Book::query()->with($this->getRelationships());
        return $this->export($query, $options);
    }

    /**
     * Export filtered books to CSV
     *
     * @param Builder $query
     * @param array $options
     * @return string File path of exported CSV
     */
    public function export(Builder $query, array $options = []): string
    {
        try {
            // Apply filters
            $query = $this->applyFilters($query, $options);

            // Generate filename
            $filename = $this->generateFilename($options);
            $filePath = $this->config['storage']['exports'] . '/' . $filename;

            // Ensure directory exists
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Open file for writing
            $handle = fopen($filePath, 'w');
            if (!$handle) {
                throw new Exception("Unable to create export file: {$filePath}");
            }

            // Write BOM for Excel compatibility
            if ($options['include_bom'] ?? true) {
                fputs($handle, "\xEF\xBB\xBF");
            }

            // Write headers
            $this->writeHeaders($handle, $options);

            // Write data rows
            $this->writeDataRows($handle, $query, $options);

            fclose($handle);

            Log::info('CSV export completed', [
                'file' => $filename,
                'records' => $query->count(),
            ]);

            return $filePath;

        } catch (Exception $e) {
            Log::error('CSV Export Error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Export with specific filters
     *
     * @param array $filters
     * @param array $options
     * @return string
     */
    public function exportFiltered(array $filters, array $options = []): string
    {
        $query = Book::query()->with($this->getRelationships());
        return $this->export($query, array_merge($options, ['filters' => $filters]));
    }

    /**
     * Apply filters to query
     *
     * @param Builder $query
     * @param array $options
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $options): Builder
    {
        $filters = $options['filters'] ?? [];

        // Date range filter
        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        if (!empty($filters['updated_from'])) {
            $query->where('updated_at', '>=', $filters['updated_from']);
        }
        if (!empty($filters['updated_to'])) {
            $query->where('updated_at', '<=', $filters['updated_to']);
        }

        // Access level filter
        if (!empty($filters['access_level'])) {
            if (is_array($filters['access_level'])) {
                $query->whereIn('access_level', $filters['access_level']);
            } else {
                $query->where('access_level', $filters['access_level']);
            }
        }

        // Collection filter
        if (!empty($filters['collection_id'])) {
            if (is_array($filters['collection_id'])) {
                $query->whereIn('collection_id', $filters['collection_id']);
            } else {
                $query->where('collection_id', $filters['collection_id']);
            }
        }

        // Language filter
        if (!empty($filters['language_id'])) {
            $query->whereHas('languages', function ($q) use ($filters) {
                if (is_array($filters['language_id'])) {
                    $q->whereIn('language_id', $filters['language_id']);
                } else {
                    $q->where('language_id', $filters['language_id']);
                }
            });
        }

        // Active/Featured filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Year range
        if (!empty($filters['year_from'])) {
            $query->where('publication_year', '>=', $filters['year_from']);
        }
        if (!empty($filters['year_to'])) {
            $query->where('publication_year', '<=', $filters['year_to']);
        }

        return $query;
    }

    /**
     * Write CSV headers
     *
     * @param resource $handle
     * @param array $options
     * @return void
     */
    protected function writeHeaders($handle, array $options): void
    {
        // Get headers from field mapping
        $headers = array_keys($this->fieldMapping);

        // Filter out null mappings and internal fields
        $headers = array_filter($headers, function ($header) {
            return $this->fieldMapping[$header] !== null &&
                   !str_contains($header, 'books.') &&
                   $header !== 'Name match check';
        });

        // Write header row
        fputcsv($handle, $headers);

        // Optionally write database mapping row
        if ($options['include_mapping_row'] ?? true) {
            $mappingRow = array_map(function ($header) {
                return $this->fieldMapping[$header] ?? '';
            }, $headers);
            fputcsv($handle, $mappingRow);
        }

        // Store headers for data writing
        $this->headers = $headers;
    }

    /**
     * Write data rows
     *
     * @param resource $handle
     * @param Builder $query
     * @param array $options
     * @return void
     */
    protected function writeDataRows($handle, Builder $query, array $options): void
    {
        $chunkSize = $options['chunk_size'] ?? 100;

        $query->chunk($chunkSize, function ($books) use ($handle, $options) {
            foreach ($books as $book) {
                $rowData = $this->formatBookForCsv($book, $options);
                fputcsv($handle, $rowData);
            }
        });
    }

    /**
     * Format book data for CSV export
     *
     * @param Book $book
     * @param array $options
     * @return array
     */
    protected function formatBookForCsv(Book $book, array $options): array
    {
        $row = [];

        foreach ($this->headers as $header) {
            $fieldName = $this->fieldMapping[$header];

            if (!$fieldName) {
                $row[] = '';
                continue;
            }

            $value = $this->getFieldValue($book, $fieldName, $options);
            $row[] = $value;
        }

        return $row;
    }

    /**
     * Get field value for export
     *
     * @param Book $book
     * @param string $fieldName
     * @param array $options
     * @return string
     */
    protected function getFieldValue(Book $book, string $fieldName, array $options): string
    {
        // Direct book fields
        $directFields = [
            'internal_id', 'palm_code', 'title', 'subtitle', 'translated_title',
            'physical_type', 'publication_year', 'pages', 'description', 'toc',
            'notes_issue', 'notes_content', 'contact', 'vla_standard', 'vla_benchmark'
        ];

        if (in_array($fieldName, $directFields)) {
            return (string) ($book->$fieldName ?? '');
        }

        // Access level (reverse mapping: full/unavailable/limited → Y/N/L)
        if ($fieldName === 'access_level') {
            return match($book->access_level) {
                'full' => 'Y',
                'unavailable' => 'N',
                'limited' => 'L',
                default => 'N'
            };
        }

        // Collection
        if ($fieldName === 'collection') {
            return $book->collection?->name ?? '';
        }

        // Publisher
        if ($fieldName === 'publisher') {
            return $book->publisher?->name ?? '';
        }

        if ($fieldName === 'publisher_program') {
            return $book->publisher?->program_name ?? '';
        }

        // Languages
        if ($fieldName === 'primary_language') {
            $primaryLang = $book->languages->where('pivot.is_primary', true)->first();
            return $primaryLang?->name ?? '';
        }

        if ($fieldName === 'primary_language_iso') {
            $primaryLang = $book->languages->where('pivot.is_primary', true)->first();
            return $primaryLang?->code ?? '';
        }

        if ($fieldName === 'secondary_language') {
            $secondaryLang = $book->languages->where('pivot.is_primary', false)->first();
            return $secondaryLang?->name ?? '';
        }

        if ($fieldName === 'secondary_language_iso') {
            $secondaryLang = $book->languages->where('pivot.is_primary', false)->first();
            return $secondaryLang?->code ?? '';
        }

        // Geographic locations
        if ($fieldName === 'geographic_island' || $fieldName === 'geographic_state') {
            return $this->joinMultiValue($book->geographicLocations->pluck('name')->toArray());
        }

        // Creators
        if (str_starts_with($fieldName, 'author_')) {
            return $this->getCreatorByIndex($book, 'author', $fieldName);
        }

        if (str_starts_with($fieldName, 'illustrator_')) {
            return $this->getCreatorByIndex($book, 'illustrator', $fieldName);
        }

        if (str_starts_with($fieldName, 'other_creator_')) {
            if (str_ends_with($fieldName, '_role')) {
                return $this->getCreatorRole($book, $fieldName);
            }
            return $this->getOtherCreator($book, $fieldName);
        }

        // Classifications
        if (str_starts_with($fieldName, 'classification_')) {
            return $this->getClassificationValues($book, $fieldName);
        }

        // Keywords
        if ($fieldName === 'keywords') {
            return $this->joinMultiValue($book->keywords->pluck('keyword')->toArray());
        }

        // Files
        if ($fieldName === 'pdf_filename') {
            return $book->primaryPdf()?->filename ?? '';
        }

        if ($fieldName === 'thumbnail_filename') {
            return $book->primaryThumbnail()?->filename ?? '';
        }

        if ($fieldName === 'digital_source') {
            return $book->primaryPdf()?->digital_source ?? '';
        }

        if ($fieldName === 'audio_files') {
            return $this->joinMultiValue($book->audioFiles->pluck('filename')->toArray());
        }

        if ($fieldName === 'video_urls') {
            return $this->joinMultiValue($book->videoFiles->pluck('external_url')->toArray());
        }

        // Library references
        if (str_starts_with($fieldName, 'uh_')) {
            return $this->getLibraryReference($book, 'UH', $fieldName);
        }

        if (str_starts_with($fieldName, 'com_')) {
            return $this->getLibraryReference($book, 'COM', $fieldName);
        }

        // Book relationships
        if (str_starts_with($fieldName, 'related_')) {
            return $this->getRelatedBooks($book, $fieldName);
        }

        return '';
    }

    /**
     * Get relationships to eager load
     *
     * @return array
     */
    protected function getRelationships(): array
    {
        return [
            'collection',
            'publisher',
            'languages',
            'bookCreators.creator',
            'classificationValues.classificationType',
            'geographicLocations',
            'keywords',
            'files',
            'libraryReferences',
            'bookRelationships',
        ];
    }

    /**
     * Generate export filename
     *
     * @param array $options
     * @return string
     */
    protected function generateFilename(array $options): string
    {
        $prefix = $options['filename_prefix'] ?? 'books-export';
        $timestamp = now()->format('Y-m-d_His');
        $extension = $options['format'] ?? 'csv';

        return "{$prefix}_{$timestamp}.{$extension}";
    }

    /**
     * Join multiple values with separator
     *
     * @param array $values
     * @return string
     */
    protected function joinMultiValue(array $values): string
    {
        return implode($this->separator, array_filter($values));
    }

    /**
     * Get creator by index and type
     *
     * @param Book $book
     * @param string $type
     * @param string $fieldName
     * @return string
     */
    protected function getCreatorByIndex(Book $book, string $type, string $fieldName): string
    {
        // Extract index from field name (e.g., 'author_1' -> 0)
        preg_match('/_(\d+)$/', $fieldName, $matches);
        $index = isset($matches[1]) ? (int)$matches[1] - 1 : 0;

        $creators = $book->bookCreators->where('creator_type', $type)->sortBy('sort_order')->values();

        return $creators->get($index)?->creator?->name ?? '';
    }

    /**
     * Get other creator by index
     *
     * @param Book $book
     * @param string $fieldName
     * @return string
     */
    protected function getOtherCreator(Book $book, string $fieldName): string
    {
        // Extract index (e.g., 'other_creator_1' -> 0)
        preg_match('/_(\d+)$/', $fieldName, $matches);
        $index = isset($matches[1]) ? (int)$matches[1] - 1 : 0;

        $otherCreators = $book->bookCreators->whereNotIn('creator_type', ['author', 'illustrator'])->sortBy('sort_order')->values();

        return $otherCreators->get($index)?->creator?->name ?? '';
    }

    /**
     * Get creator role
     *
     * @param Book $book
     * @param string $fieldName
     * @return string
     */
    protected function getCreatorRole(Book $book, string $fieldName): string
    {
        // Extract index (e.g., 'other_creator_1_role' -> 0)
        preg_match('/_(\d+)_role$/', $fieldName, $matches);
        $index = isset($matches[1]) ? (int)$matches[1] - 1 : 0;

        $otherCreators = $book->bookCreators->whereNotIn('creator_type', ['author', 'illustrator'])->sortBy('sort_order')->values();

        return $otherCreators->get($index)?->role_description ?? '';
    }

    /**
     * Get classification values
     *
     * @param Book $book
     * @param string $fieldName
     * @return string
     */
    protected function getClassificationValues(Book $book, string $fieldName): string
    {
        $typeMapping = $this->config['classification_type_mapping'];
        $typeSlug = $typeMapping[$fieldName] ?? null;

        if (!$typeSlug) {
            return '';
        }

        $values = $book->classificationValues
            ->filter(function ($cv) use ($typeSlug) {
                return $cv->classificationType->slug === $typeSlug;
            })
            ->pluck('value')
            ->toArray();

        return $this->joinMultiValue($values);
    }

    /**
     * Get library reference field
     *
     * @param Book $book
     * @param string $libraryCode
     * @param string $fieldName
     * @return string
     */
    protected function getLibraryReference(Book $book, string $libraryCode, string $fieldName): string
    {
        $reference = $book->libraryReferences->where('library_code', $libraryCode)->first();

        if (!$reference) {
            return '';
        }

        // Extract field type from field name
        if (str_contains($fieldName, 'reference_number')) {
            return $reference->reference_number ?? '';
        }
        if (str_contains($fieldName, 'call_number')) {
            return $reference->call_number ?? '';
        }
        if (str_contains($fieldName, 'link')) {
            return $reference->catalog_link ?? '';
        }
        if (str_contains($fieldName, 'note')) {
            return $reference->notes ?? '';
        }

        return '';
    }

    /**
     * Get related books
     *
     * @param Book $book
     * @param string $fieldName
     * @return string
     */
    protected function getRelatedBooks(Book $book, string $fieldName): string
    {
        $typeMapping = $this->config['relationship_type_mapping'];
        $relationshipType = $typeMapping[$fieldName] ?? null;

        if (!$relationshipType) {
            return '';
        }

        $relatedIds = $book->bookRelationships
            ->where('relationship_type', $relationshipType)
            ->pluck('relationship_code')
            ->toArray();

        return $this->joinMultiValue($relatedIds);
    }
}
