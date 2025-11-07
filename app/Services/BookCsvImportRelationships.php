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
        return Language::where('name', $nameOrCode)->first();
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
        if (str_contains($role, 'compil')) {
            return 'compiler';
        }
        if (str_contains($role, 'adapt')) {
            return 'adapter';
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

            // Get classification type
            $classificationType = ClassificationType::where('slug', $typeSlug)->first();
            if (!$classificationType) {
                continue;
            }

            // Parse multiple values (pipe-separated)
            $values = $this->splitMultiValue($data[$dataKey]);

            foreach ($values as $value) {
                $classificationValue = ClassificationValue::where('classification_type_id', $classificationType->id)
                    ->where('value', $value)
                    ->first();

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

        // Build file path based on type
        $extension = $type === 'pdf' ? '.pdf' : ($type === 'audio' ? '.mp3' : '.png');
        if (!str_ends_with(strtolower($filename), $extension)) {
            $filename .= $extension;
        }

        $filePath = $this->config['file_paths'][$type] . '/' . basename($filename);

        BookFile::create([
            'book_id' => $book->id,
            'file_type' => $type,
            'file_path' => $filePath,
            'filename' => basename($filename),
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

        // University of Hawaii
        if (!empty($data['uh_call_number']) || !empty($data['uh_reference_number'])) {
            LibraryReference::create([
                'book_id' => $book->id,
                'library_code' => 'UH',
                'library_name' => 'University of Hawaii Library',
                'reference_number' => $data['uh_reference_number'] ?? null,
                'call_number' => $data['uh_call_number'] ?? null,
                'catalog_link' => $data['uh_catalog_link'] ?? null,
                'notes' => $data['uh_notes'] ?? null,
            ]);
        }

        // College of Micronesia
        if (!empty($data['com_call_number']) || !empty($data['com_reference_number'])) {
            LibraryReference::create([
                'book_id' => $book->id,
                'library_code' => 'COM',
                'library_name' => 'College of Micronesia Library',
                'reference_number' => $data['com_reference_number'] ?? null,
                'call_number' => $data['com_call_number'] ?? null,
                'notes' => $data['com_notes'] ?? null,
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

            $relatedIds = $this->splitMultiValue($data[$dataKey]);

            foreach ($relatedIds as $relatedId) {
                // Find related book by internal_id
                $relatedBook = Book::where('internal_id', $relatedId)->first();

                if ($relatedBook) {
                    $book->bookRelationships()->create([
                        'related_book_id' => $relatedBook->id,
                        'relationship_type' => $relationshipType,
                        'relationship_code' => $relatedId,
                    ]);
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
