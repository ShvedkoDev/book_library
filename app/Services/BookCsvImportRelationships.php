<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Collection;
use App\Models\Publisher;
use App\Models\Language;
use App\Models\Creator;
use App\Models\ClassificationValue;
use App\Models\ClassificationType;
use App\Models\GeographicLocation;
use App\Models\BookKeyword;
use App\Models\BookFile;
use App\Models\LibraryReference;
use App\Models\BookIdentifier;
use App\Models\BookRelationship;
use Illuminate\Support\Facades\Log;

trait BookCsvImportRelationships
{
    /**
     * Resolve or create collection
     */
    protected function resolveCollection(string $name, array $options): ?Collection
    {
        $collection = Collection::where('name', $name)->first();

        if (!$collection && ($options['create_missing_relations'] ?? false)) {
            $collection = Collection::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'description' => null,
                'is_active' => true,
            ]);
        }

        return $collection;
    }

    /**
     * Resolve or create publisher
     */
    protected function resolvePublisher(string $name, ?string $programName, array $options): ?Publisher
    {
        $publisher = Publisher::where('name', $name)->first();

        if (!$publisher && ($options['create_missing_relations'] ?? false)) {
            $publisher = Publisher::create([
                'name' => $name,
                'program_name' => $programName,
                'is_active' => true,
            ]);
        } elseif ($publisher && $programName) {
            // Update program name if provided
            $publisher->update(['program_name' => $programName]);
        }

        return $publisher;
    }

    /**
     * Attach languages to book
     */
    protected function attachLanguages(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookLanguages()->delete();
        }

        $languagesToAttach = [];

        // Primary language
        if (!empty($data['primary_language'])) {
            $language = $this->resolveLanguage($data['primary_language'], $data['primary_language_iso'] ?? null);
            if ($language) {
                $languagesToAttach[$language->id] = ['is_primary' => true];
            }
        }

        // Secondary language
        if (!empty($data['secondary_language'])) {
            $language = $this->resolveLanguage($data['secondary_language'], $data['secondary_language_iso'] ?? null);
            if ($language) {
                $languagesToAttach[$language->id] = ['is_primary' => false];
            }
        }

        if (!empty($languagesToAttach)) {
            $book->languages()->sync($languagesToAttach);
        }
    }

    /**
     * Resolve language by name or ISO code
     */
    protected function resolveLanguage(string $nameOrCode, ?string $isoCode): ?Language
    {
        // Try by ISO code first
        if ($isoCode) {
            $language = Language::where('code', $isoCode)->first();
            if ($language) {
                return $language;
            }
        }

        // Try by name
        $language = Language::where('name', $nameOrCode)->first();

        // Create if not found and auto-create is enabled
        if (!$language) {
            $language = Language::create([
                'name' => $nameOrCode,
                'code' => $isoCode ?? strtolower(substr($nameOrCode, 0, 3)),
                'native_name' => $nameOrCode,
                'is_active' => true,
            ]);

            \Illuminate\Support\Facades\Log::info("Created new language: {$nameOrCode} ({$isoCode})");
        }

        return $language;
    }

    /**
     * Attach creators (authors, illustrators, etc.) to book
     */
    protected function attachCreators(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookCreators()->delete();
        }

        $sortOrder = 0;

        // Authors
        foreach (['author_1', 'author_2', 'author_3'] as $key) {
            if (!empty($data[$key])) {
                $this->attachCreator($book, $data[$key], 'author', null, $sortOrder++, $options);
            }
        }

        // Other creators with roles
        foreach (['other_creator_1', 'other_creator_2'] as $index => $key) {
            if (!empty($data[$key])) {
                $roleKey = $key . '_role';
                $role = $data[$roleKey] ?? null;
                $type = $this->determineCreatorType($role);
                $this->attachCreator($book, $data[$key], $type, $role, $sortOrder++, $options);
            }
        }

        // Illustrators
        foreach (['illustrator_1', 'illustrator_2', 'illustrator_3', 'illustrator_4', 'illustrator_5'] as $key) {
            if (!empty($data[$key])) {
                $this->attachCreator($book, $data[$key], 'illustrator', null, $sortOrder++, $options);
            }
        }
    }

    /**
     * Attach single creator to book
     */
    protected function attachCreator(Book $book, string $name, string $type, ?string $role, int $sortOrder, array $options): void
    {
        $creator = Creator::where('name', $name)->first();

        if (!$creator && ($options['create_missing_relations'] ?? false)) {
            $creator = Creator::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'is_active' => true,
            ]);
        }

        if ($creator) {
            $book->bookCreators()->create([
                'creator_id' => $creator->id,
                'creator_type' => $type,
                'role_description' => $role,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    /**
     * Determine creator type from role description
     * Valid ENUM values: author, illustrator, editor, translator, contributor
     */
    protected function determineCreatorType(?string $role): string
    {
        if (!$role) {
            return 'contributor';
        }

        $role = strtolower($role);

        if (str_contains($role, 'translat')) {
            return 'translator';
        }
        if (str_contains($role, 'edit')) {
            return 'editor';
        }
        if (str_contains($role, 'illustrat')) {
            return 'illustrator';
        }
        if (str_contains($role, 'author') || str_contains($role, 'writ')) {
            return 'author';
        }
        // Map adapt, compile, retold, and other roles to contributor
        if (str_contains($role, 'compil') || str_contains($role, 'adapt') || str_contains($role, 'retold')) {
            return 'contributor';
        }

        return 'contributor';
    }

    /**
     * Attach classifications to book
     */
    protected function attachClassifications(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookClassifications()->delete();
        }

        $classificationMapping = $this->config['classification_type_mapping'];

        foreach ($classificationMapping as $dataKey => $typeSlug) {
            if (empty($data[$dataKey])) {
                continue;
            }

            // Get or create classification type
            $classificationType = ClassificationType::where('slug', $typeSlug)->first();

            if (!$classificationType) {
                // Auto-create classification type from slug
                $name = ucwords(str_replace(['-', '_'], ' ', $typeSlug));

                $classificationType = ClassificationType::create([
                    'name' => $name,
                    'slug' => $typeSlug,
                    'description' => "Auto-created from CSV import",
                    'allow_multiple' => true,
                    'use_for_filtering' => true,
                    'sort_order' => 999,
                    'is_active' => true,
                ]);

                \Illuminate\Support\Facades\Log::info("Created new classification type: {$name} ({$typeSlug})");
            }

            // Parse multiple values (pipe-separated)
            $values = $this->splitMultiValue($data[$dataKey]);

            foreach ($values as $value) {
                $classificationValue = ClassificationValue::where('classification_type_id', $classificationType->id)
                    ->where('value', $value)
                    ->first();

                // Create classification value if it doesn't exist
                if (!$classificationValue) {
                    $classificationValue = ClassificationValue::create([
                        'classification_type_id' => $classificationType->id,
                        'value' => $value,
                        'slug' => \Illuminate\Support\Str::slug($value),
                        'description' => null,
                        'sort_order' => 999,
                        'is_active' => true,
                    ]);

                    \Illuminate\Support\Facades\Log::info("Created new classification value: {$value} for type {$typeSlug}");
                }

                if ($classificationValue) {
                    $book->bookClassifications()->create([
                        'classification_value_id' => $classificationValue->id,
                    ]);
                }
            }
        }
    }

    /**
     * Attach geographic locations to book
     */
    protected function attachGeographicLocations(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookLocations()->delete();
        }

        $locations = [];

        // Islands
        if (!empty($data['geographic_island'])) {
            $islandNames = $this->splitMultiValue($data['geographic_island']);
            foreach ($islandNames as $name) {
                $location = GeographicLocation::where('name', $name)->first();

                // Create location if it doesn't exist
                if (!$location) {
                    $location = GeographicLocation::create([
                        'name' => $name,
                        'slug' => \Illuminate\Support\Str::slug($name),
                        'type' => 'island',
                        'is_active' => true,
                    ]);

                    \Illuminate\Support\Facades\Log::info("Created new geographic location (island): {$name}");
                }

                if ($location) {
                    $locations[] = $location->id;
                }
            }
        }

        // States
        if (!empty($data['geographic_state'])) {
            $stateNames = $this->splitMultiValue($data['geographic_state']);
            foreach ($stateNames as $name) {
                $location = GeographicLocation::where('name', $name)->first();

                // Create location if it doesn't exist
                if (!$location) {
                    $location = GeographicLocation::create([
                        'name' => $name,
                        'slug' => \Illuminate\Support\Str::slug($name),
                        'type' => 'state',
                        'is_active' => true,
                    ]);

                    \Illuminate\Support\Facades\Log::info("Created new geographic location (state): {$name}");
                }

                if ($location) {
                    $locations[] = $location->id;
                }
            }
        }

        // Attach locations
        foreach (array_unique($locations) as $locationId) {
            $book->bookLocations()->create([
                'location_id' => $locationId,
            ]);
        }
    }

    /**
     * Attach keywords to book
     */
    protected function attachKeywords(Book $book, array $data, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->keywords()->delete();
        }

        if (!empty($data['keywords'])) {
            $keywords = $this->splitMultiValue($data['keywords']);

            foreach ($keywords as $keyword) {
                $book->keywords()->create([
                    'keyword' => trim($keyword),
                ]);
            }
        }
    }

    /**
     * Attach files to book
     */
    protected function attachFiles(Book $book, array $data, array $options): void
    {
        // Primary PDF
        if (!empty($data['pdf_filename'])) {
            $this->attachFile($book, 'pdf', $data['pdf_filename'], $data['digital_source'] ?? null, true);
        }

        // Primary Thumbnail
        if (!empty($data['thumbnail_filename'])) {
            $this->attachFile($book, 'thumbnail', $data['thumbnail_filename'], null, true);
        }

        // Alternative PDF
        if (!empty($data['pdf_filename_alt'])) {
            $this->attachFile($book, 'pdf', $data['pdf_filename_alt'], $data['digital_source_alt'] ?? null, false);
        }

        // Alternative Thumbnail
        if (!empty($data['thumbnail_filename_alt'])) {
            $this->attachFile($book, 'thumbnail', $data['thumbnail_filename_alt'], null, false);
        }

        // Audio files
        if (!empty($data['audio_files'])) {
            $audioFiles = $this->splitMultiValue($data['audio_files']);
            foreach ($audioFiles as $index => $filename) {
                $this->attachFile($book, 'audio', $filename, null, $index === 0);
            }
        }

        // Video URLs
        if (!empty($data['video_urls'])) {
            $videoUrls = $this->splitMultiValue($data['video_urls']);
            foreach ($videoUrls as $index => $url) {
                $this->attachVideoUrl($book, $url, $index === 0);
            }
        }
    }

    /**
     * Attach single file to book
     */
    protected function attachFile(Book $book, string $type, string $filename, ?string $digitalSource, bool $isPrimary): void
    {
        // Clean filename (remove extension if in data, we'll determine path)
        $filename = trim($filename);

        // Clean digital source for UTF-8 encoding
        if ($digitalSource) {
            $digitalSource = $this->cleanTextEncoding($digitalSource);
        }

        // Build file path based on type
        $extension = $type === 'pdf' ? '.pdf' : ($type === 'audio' ? '.mp3' : '.png');
        if (!str_ends_with(strtolower($filename), $extension)) {
            $filename .= $extension;
        }

        $filePath = $this->config['file_paths'][$type] . '/' . basename($filename);

        // Clean file path for UTF-8
        $filePath = $this->cleanTextEncoding($filePath);
        $cleanFilename = $this->cleanTextEncoding(basename($filename));

        BookFile::create([
            'book_id' => $book->id,
            'file_type' => $type,
            'file_path' => $filePath,
            'filename' => $cleanFilename,
            'mime_type' => $this->getMimeType($type),
            'is_primary' => $isPrimary,
            'digital_source' => $digitalSource,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Attach video URL to book
     */
    protected function attachVideoUrl(Book $book, string $url, bool $isPrimary): void
    {
        BookFile::create([
            'book_id' => $book->id,
            'file_type' => 'video',
            'external_url' => $url,
            'is_primary' => $isPrimary,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Get MIME type for file type
     */
    protected function getMimeType(string $type): string
    {
        return match($type) {
            'pdf' => 'application/pdf',
            'thumbnail' => 'image/png',
            'audio' => 'audio/mpeg',
            'video' => 'video/mp4',
            default => 'application/octet-stream',
        };
    }

    /**
     * Attach library references to book
     */
    protected function attachLibraryReferences(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->libraryReferences()->delete();
        }

        // Define library mappings (5 libraries with main_link and alt_link)
        $libraryMappings = [
            [
                'code' => 'UH',
                'name' => 'University of Hawaii',
                'reference' => 'uh_reference_number',
                'call_number' => 'uh_call_number',
                'catalog_link' => 'uh_catalog_link',
                'main_link' => 'library_link_uh',           // NEW: Column BH
                'alt_link' => 'library_link_uh_alt',        // NEW: Column BI
                'notes' => 'uh_notes',
            ],
            [
                'code' => 'COM-FSM',
                'name' => 'College of Micronesia - FSM',
                'reference' => 'com_reference_number',
                'call_number' => 'com_call_number',
                'main_link' => 'library_link_com_fsm',      // NEW: Column BJ
                'alt_link' => 'library_link_com_fsm_alt',   // NEW: Column BK
                'notes' => 'com_notes',
            ],
            [
                'code' => 'MARC',
                'name' => 'University of Guam (MARC)',
                'main_link' => 'library_link_marc',         // NEW: Column BL
                'alt_link' => 'library_link_marc_alt',      // NEW: Column BM
            ],
            [
                'code' => 'MICSEM',
                'name' => 'Micronesian Seminar',
                'main_link' => 'library_link_micsem',       // NEW: Column BN
                'alt_link' => 'library_link_micsem_alt',    // NEW: Column BO
            ],
            [
                'code' => 'LIB5',
                'name' => 'Library #5 (Reserved)',
                'main_link' => 'library_link_5',            // NEW: Column BP
                'alt_link' => 'library_link_5_alt',         // NEW: Column BQ
            ],
        ];

        // Process each library
        foreach ($libraryMappings as $library) {
            // Check if library has any data
            $hasData = false;
            foreach (['reference', 'call_number', 'catalog_link', 'main_link', 'alt_link', 'notes'] as $field) {
                if (isset($library[$field]) && !empty($data[$library[$field]])) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                LibraryReference::create([
                    'book_id' => $book->id,
                    'library_code' => $library['code'],
                    'library_name' => $library['name'],
                    'reference_number' => isset($library['reference']) ? ($data[$library['reference']] ?? null) : null,
                    'call_number' => isset($library['call_number']) ? ($data[$library['call_number']] ?? null) : null,
                    'catalog_link' => isset($library['catalog_link']) ? ($data[$library['catalog_link']] ?? null) : null,
                    'main_link' => isset($library['main_link']) ? ($data[$library['main_link']] ?? null) : null,     // NEW
                    'alt_link' => isset($library['alt_link']) ? ($data[$library['alt_link']] ?? null) : null,        // NEW
                    'notes' => isset($library['notes']) ? ($data[$library['notes']] ?? null) : null,
                ]);
            }
        }
    }

    /**
     * Attach book identifiers (OCLC, ISBN, Other)
     */
    protected function attachBookIdentifiers(Book $book, array $data, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookIdentifiers()->delete();
        }

        // OCLC Number (Column BR)
        if (!empty($data['oclc_number'])) {
            BookIdentifier::create([
                'book_id' => $book->id,
                'identifier_type' => BookIdentifier::TYPE_OCLC,
                'identifier_value' => trim($data['oclc_number']),
            ]);
        }

        // ISBN Number (Column BS)
        if (!empty($data['isbn_number'])) {
            $isbn = trim($data['isbn_number']);

            // Determine if it's ISBN-10 or ISBN-13
            $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
            $type = strlen($cleanIsbn) === 13 ? BookIdentifier::TYPE_ISBN13 : BookIdentifier::TYPE_ISBN;

            BookIdentifier::create([
                'book_id' => $book->id,
                'identifier_type' => $type,
                'identifier_value' => $isbn,
            ]);
        }

        // Other Number (Column BT)
        if (!empty($data['other_number'])) {
            BookIdentifier::create([
                'book_id' => $book->id,
                'identifier_type' => BookIdentifier::TYPE_OTHER,
                'identifier_value' => trim($data['other_number']),
            ]);
        }
    }

    /**
     * Attach book relationships (related books)
     */
    protected function attachBookRelationships(Book $book, array $data, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookRelationships()->delete();
        }

        $relationshipMapping = $this->config['relationship_type_mapping'];

        foreach ($relationshipMapping as $dataKey => $relationshipType) {
            if (empty($data[$dataKey])) {
                continue;
            }

            // The CSV contains relationship CODES (like 'YSCI'), not book IDs
            // We need to find all books with the same code and create relationships
            $relationshipCodes = $this->splitMultiValue($data[$dataKey]);

            foreach ($relationshipCodes as $relationshipCode) {
                // Find all books that have this same relationship code
                // We'll create relationships later in a post-processing step
                // For now, just store the code so we can find related books later
                
                // Store a placeholder relationship with the code
                // The related_book_id will be NULL initially
                $book->bookRelationships()->updateOrCreate(
                    [
                        'relationship_type' => $relationshipType,
                        'relationship_code' => $relationshipCode,
                    ],
                    [
                        'related_book_id' => null,  // Will be filled in post-processing
                        'description' => 'Pending relationship matching',
                    ]
                );
            }
        }
    }

    /**
     * Post-process book relationships to match books with same codes
     * This should be called after all books are imported
     */
    public function processBookRelationships(): void
    {
        // Get all book_relationships with NULL related_book_id grouped by code and type
        $pendingRelationships = \DB::table('book_relationships')
            ->whereNull('related_book_id')
            ->whereNotNull('relationship_code')
            ->get()
            ->groupBy(function($item) {
                return $item->relationship_type . '::' . $item->relationship_code;
            });

        foreach ($pendingRelationships as $groupKey => $relationships) {
            // Skip invalid relationship codes (like "*TRUE if Translated-title identical*")
            $sampleCode = $relationships->first()->relationship_code;
            if (empty($sampleCode) || 
                str_starts_with($sampleCode, '*') ||
                str_contains(strtolower($sampleCode), 'true') ||
                str_contains(strtolower($sampleCode), 'false')) {
                // Delete all invalid relationships in this group
                $ids = $relationships->pluck('id')->toArray();
                \DB::table('book_relationships')->whereIn('id', $ids)->delete();
                continue;
            }

            // Get all book IDs in this relationship group
            $bookIds = $relationships->pluck('book_id')->unique()->toArray();
            
            // For each book in the group, link it to all other books in the same group
            foreach ($relationships as $relationship) {
                $relatedBookIds = array_diff($bookIds, [$relationship->book_id]);
                
                if (empty($relatedBookIds)) {
                    // No other books in this group, delete orphan
                    \DB::table('book_relationships')->where('id', $relationship->id)->delete();
                    continue;
                }
                
                // Update the first relationship
                $firstRelatedId = array_shift($relatedBookIds);
                \DB::table('book_relationships')
                    ->where('id', $relationship->id)
                    ->update([
                        'related_book_id' => $firstRelatedId,
                        'description' => null,
                        'updated_at' => now(),
                    ]);
                
                // Create additional relationships for remaining books
                foreach ($relatedBookIds as $relatedBookId) {
                    // Check if relationship already exists
                    $exists = \DB::table('book_relationships')
                        ->where('book_id', $relationship->book_id)
                        ->where('related_book_id', $relatedBookId)
                        ->where('relationship_type', $relationship->relationship_type)
                        ->exists();
                    
                    if (!$exists) {
                        \DB::table('book_relationships')->insert([
                            'book_id' => $relationship->book_id,
                            'related_book_id' => $relatedBookId,
                            'relationship_type' => $relationship->relationship_type,
                            'relationship_code' => $relationship->relationship_code,
                            'description' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Split multi-value field by separator
     */
    protected function splitMultiValue(string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $values = explode($this->separator, $value);

        return array_filter(array_map('trim', $values));
    }
}
