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
                'program_name' => $programName,  // Only set on creation, never update
                'is_active' => true,
            ]);
        }
        // REMOVED: The program_name update logic that was causing the bug
        // Program/partner names are now stored per-book in books.program_partner_name
        // to allow different books from the same publisher to have different partners

        return $publisher;
    }

    /**
     * Attach languages to book
     * Supports pipe-separated values for multiple languages
     */
    protected function attachLanguages(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookLanguages()->delete();
        }

        $languagesToAttach = [];

        // Primary language(s) - can be pipe-separated
        if (!empty($data['primary_language'])) {
            $primaryLanguages = $this->splitMultiValue($data['primary_language']);
            foreach ($primaryLanguages as $langName) {
                $language = $this->resolveLanguage($langName, null);
                if ($language) {
                    $languagesToAttach[$language->id] = ['is_primary' => true];
                }
            }
        }

        // Secondary language(s) - can be pipe-separated
        if (!empty($data['secondary_language'])) {
            $secondaryLanguages = $this->splitMultiValue($data['secondary_language']);
            foreach ($secondaryLanguages as $langName) {
                $language = $this->resolveLanguage($langName, null);
                if ($language) {
                    $languagesToAttach[$language->id] = ['is_primary' => false];
                }
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
        // Generate the code we'll use
        $codeToUse = $isoCode ?? strtolower(substr($nameOrCode, 0, 3));

        // Try by ISO code first
        if ($isoCode) {
            $language = Language::where('code', $isoCode)->first();
            if ($language) {
                return $language;
            }
        }

        // Try by name
        $language = Language::where('name', $nameOrCode)->first();
        if ($language) {
            return $language;
        }

        // Try by generated code (in case it exists with different name)
        $language = Language::where('code', $codeToUse)->first();
        if ($language) {
            return $language;
        }

        // Create if not found - use firstOrCreate for extra safety
        $language = Language::firstOrCreate(
            ['code' => $codeToUse], // Match by code
            [
                'name' => $nameOrCode,
                'native_name' => $nameOrCode,
                'is_active' => true,
            ]
        );

        if ($language->wasRecentlyCreated) {
            \Illuminate\Support\Facades\Log::info("Created new language: {$nameOrCode} ({$codeToUse})");
        }

        return $language;
    }

    /**
     * Attach creators (authors, illustrators, etc.) to book
     * Supports pipe-separated values for multiple creators in each field
     */
    protected function attachCreators(Book $book, array $data, array $options, bool $isUpdate): void
    {
        if ($isUpdate) {
            $book->bookCreators()->delete();
        }

        $sortOrder = 0;

        // Authors - each field can contain pipe-separated author names
        foreach (['author_1', 'author_2', 'author_3'] as $key) {
            if (!empty($data[$key])) {
                // Support pipe-separated author names
                $authors = $this->splitMultiValue($data[$key]);
                foreach ($authors as $authorName) {
                    $this->attachCreator($book, $authorName, 'author', null, $sortOrder++, $options);
                }
            }
        }

        // Other creators with roles - can have pipe-separated values
        foreach (['other_creator_1', 'other_creator_2', 'other_creator_3'] as $index => $key) {
            if (!empty($data[$key])) {
                $roleKey = $key . '_role';
                $role = $data[$roleKey] ?? null;

                // Support pipe-separated creator names
                $creators = $this->splitMultiValue($data[$key]);
                foreach ($creators as $creatorName) {
                    $type = $this->determineCreatorType($role);
                    $this->attachCreator($book, $creatorName, $type, $role, $sortOrder++, $options);
                }
            }
        }

        // Illustrators - each field can contain pipe-separated illustrator names
        foreach (['illustrator_1', 'illustrator_2', 'illustrator_3', 'illustrator_4', 'illustrator_5'] as $key) {
            if (!empty($data[$key])) {
                // Support pipe-separated illustrator names
                $illustrators = $this->splitMultiValue($data[$key]);
                foreach ($illustrators as $illustratorName) {
                    $this->attachCreator($book, $illustratorName, 'illustrator', null, $sortOrder++, $options);
                }
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

        // Define library mappings (6 libraries with main_link and alt_link)
        $libraryMappings = [
            [
                'code' => 'UH',
                'name' => 'University of Hawaii',
                'reference' => 'uh_reference_number',
                'call_number' => 'uh_call_number',
                'catalog_link' => 'uh_catalog_link',
                'main_link' => 'library_link_uh',         // Match config field_mapping
                'alt_link' => 'library_link_uh_alt',      // Match config field_mapping
                'notes' => 'uh_notes',
            ],
            [
                'code' => 'COM',
                'name' => 'College of Micronesia - FSM',
                'reference' => 'com_reference_number',
                'call_number' => 'com_call_number',
                'main_link' => 'library_link_com_fsm',    // Match config field_mapping
                'alt_link' => 'library_link_com_fsm_alt', // Match config field_mapping
                'notes' => 'com_notes',
            ],
            [
                'code' => 'UOG',
                'name' => 'University of Guam',
                'main_link' => 'library_link_uog',        // Match config field_mapping
                'alt_link' => 'library_link_uog_alt',     // Match config field_mapping
            ],
            [
                'code' => 'MICSEM',
                'name' => 'Micronesian Seminar',
                'main_link' => 'library_link_micsem',     // Match config field_mapping
                'alt_link' => 'library_link_micsem_alt',  // Match config field_mapping
            ],
            [
                'code' => 'MARC',
                'name' => 'MARC',
                'main_link' => 'library_link_marc',       // Match config field_mapping
                'alt_link' => 'library_link_marc_alt',    // Match config field_mapping
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
                    'main_link' => isset($library['main_link']) ? ($data[$library['main_link']] ?? null) : null,
                    'alt_link' => isset($library['alt_link']) ? ($data[$library['alt_link']] ?? null) : null,
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
        // Prevent timeout during relationship processing
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');
        @ignore_user_abort(true);

        // Send initial output to establish connection
        echo str_repeat(' ', 4096);
        if (function_exists('flush')) {
            @flush();
        }

        Log::info('Starting book relationships processing...');

        // Step 1: Process coded relationships (same_version, supporting, omnibus)
        Log::info('Processing coded relationships...');
        $this->processCodedRelationships();

        // Step 2: Generate translation relationships based on translated_title
        Log::info('Generating translation relationships...');
        $this->generateTranslationRelationships();

        Log::info('Book relationships processing completed.');
    }

    /**
     * Process relationships with relationship codes
     */
    protected function processCodedRelationships(): void
    {
        // Get all book_relationships with NULL related_book_id grouped by code and type
        $pendingRelationships = \DB::table('book_relationships')
            ->whereNull('related_book_id')
            ->whereNotNull('relationship_code')
            ->get()
            ->groupBy(function($item) {
                return $item->relationship_type . '::' . $item->relationship_code;
            });

        $totalGroups = $pendingRelationships->count();
        $processedGroups = 0;
        Log::info("Found {$totalGroups} relationship groups to process");

        foreach ($pendingRelationships as $groupKey => $relationships) {
            $processedGroups++;

            // Send whitespace every 5 groups to keep connection alive
            if ($processedGroups % 5 === 0) {
                echo str_repeat(' ', 1024);
                if (function_exists('flush')) {
                    @flush();
                }
            }

            if ($processedGroups % 10 === 0) {
                Log::info("Processing group {$processedGroups}/{$totalGroups}: {$groupKey}");
            }
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
     * Generate translation relationships based on identical translated_title values
     */
    protected function generateTranslationRelationships(): void
    {
        // Find books with translated titles
        $booksWithTranslations = Book::where('is_active', true)
            ->whereNotNull('translated_title')
            ->where('translated_title', '!=', '')
            ->with('languages')
            ->get();

        Log::info("Found {$booksWithTranslations->count()} books with translated titles");

        // Group books by translated title (case-insensitive, trimmed)
        $translationGroups = $booksWithTranslations->groupBy(function ($book) {
            return strtolower(trim($book->translated_title));
        })->filter(function ($group) {
            // Only keep groups with 2 or more books (translations must have multiple versions)
            return $group->count() >= 2;
        });

        $totalGroups = $translationGroups->count();
        $processedGroups = 0;
        Log::info("Found {$totalGroups} translation groups to process");

        // Create bidirectional relationships between all books in each group
        foreach ($translationGroups as $translatedTitle => $books) {
            $processedGroups++;

            // Send whitespace every 5 groups to keep connection alive
            if ($processedGroups % 5 === 0) {
                echo str_repeat(' ', 1024);
                if (function_exists('flush')) {
                    @flush();
                }
            }

            if ($processedGroups % 10 === 0) {
                Log::info("Processing translation group {$processedGroups}/{$totalGroups}");
            }
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
                        continue; // Skip same-language pairs
                    }

                    // Check if relationship already exists
                    $exists = \DB::table('book_relationships')
                        ->where('book_id', $book1->id)
                        ->where('related_book_id', $book2->id)
                        ->where('relationship_type', 'translated')
                        ->exists();

                    if (!$exists) {
                        \DB::table('book_relationships')->insert([
                            'book_id' => $book1->id,
                            'related_book_id' => $book2->id,
                            'relationship_type' => 'translated',
                            'relationship_code' => null,
                            'description' => 'Auto-generated: Identical translated title',
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
