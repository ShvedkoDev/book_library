<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CSV Import Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for book CSV import/export functionality.
    | These settings control batch sizes, file handling, and import behavior.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Batch Processing
    |--------------------------------------------------------------------------
    */
    'batch_size' => env('CSV_IMPORT_BATCH_SIZE', 100),
    'chunk_size' => env('CSV_IMPORT_CHUNK_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | File Handling
    |--------------------------------------------------------------------------
    */
    'max_file_size' => env('CSV_IMPORT_MAX_FILE_SIZE', 52428800), // 50MB in bytes
    'allowed_extensions' => ['csv', 'txt'],
    'encoding' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'imports' => storage_path('csv-imports'),
        'exports' => storage_path('csv-exports'),
        'templates' => storage_path('csv-templates'),
        'logs' => storage_path('logs/csv-imports'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Modes
    |--------------------------------------------------------------------------
    */
    'default_mode' => 'upsert', // create_only, update_only, upsert, create_duplicates

    'modes' => [
        'create_only' => 'Skip existing records, only create new ones',
        'update_only' => 'Only update existing records, skip new ones',
        'upsert' => 'Create new or update existing records (default)',
        'create_duplicates' => 'Allow duplicates with new IDs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Options (Defaults)
    |--------------------------------------------------------------------------
    */
    'options' => [
        'create_missing_relations' => true,
        'validate_file_references' => true,
        'skip_invalid_rows' => false,
        'send_completion_email' => false,
        'enable_transactions' => true,
        'run_quality_checks' => env('CSV_IMPORT_RUN_QUALITY_CHECKS', true), // Run post-import quality checks
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Value Separator
    |--------------------------------------------------------------------------
    */
    'separator' => '|',
    'alternative_separator' => ';',

    /*
    |--------------------------------------------------------------------------
    | CSV Field Mapping
    |--------------------------------------------------------------------------
    |
    | Maps CSV column names to database fields and relationships.
    | This is the authoritative mapping for all import operations.
    |
    */

    'field_mapping' => [
        // ==========================================
        // DIRECT BOOK FIELDS
        // ==========================================
        'ID' => 'internal_id',
        'PALM code' => 'palm_code',
        'Title' => 'title',
        'Sub-title' => 'subtitle',
        'Translated-title' => 'translated_title',
        'Physical type' => 'physical_type',
        'Year' => 'publication_year',
        'Pages' => 'pages',
        'TOC' => 'toc',
        'Notes related to the issue.' => 'notes_issue',
        'Notes related to content.' => 'notes_content',
        'ABSTRACT/DESCRIPTION' => 'description',
        'VLA standard' => 'vla_standard',
        'VLA benchmark' => 'vla_benchmark',
        'CONTACT' => 'contact',

        // ==========================================
        // ACCESS LEVEL (special mapping: Y/N/L → full/unavailable/limited)
        // ==========================================
        'UPLOADED' => 'access_level',

        // ==========================================
        // RELATIONAL FIELDS (many-to-many)
        // ==========================================

        // Collection (belongs to)
        'Collection' => 'collection',

        // Publisher (belongs to)
        'Publisher' => 'publisher',
        'Contributor / Project / Partner' => 'publisher_program',

        // Languages (many-to-many)
        'Language 1' => 'primary_language',
        'ISO (Language 1)' => 'primary_language_iso',
        'Language 2' => 'secondary_language',
        'ISO (Language 2)' => 'secondary_language_iso',

        // Geographic Locations (many-to-many)
        'Island' => 'geographic_island',
        'State' => 'geographic_state',

        // Creators (many-to-many with types)
        'Author' => 'author_1',
        'Author2' => 'author_2',
        'Author3' => 'author_3',
        'Other creator' => 'other_creator_1',
        'Other creator ROLE' => 'other_creator_1_role',
        'Other creator2' => 'other_creator_2',
        'Other creator2 ROLE' => 'other_creator_2_role',
        'Illustrator' => 'illustrator_1',
        'Illustrator2' => 'illustrator_2',
        'Illustrator3' => 'illustrator_3',
        'Illustrator4' => 'illustrator_4',
        'Illustrator5' => 'illustrator_5',

        // Classifications (many-to-many)
        'Purpose' => 'classification_purpose',
        'Genre' => 'classification_genre',
        'Sub-genre' => 'classification_subgenre',
        'Type' => 'classification_type',
        'Themes/Uses' => 'classification_themes',
        'Learner level' => 'classification_learner_level',

        // Keywords (one-to-many)
        'Keywords' => 'keywords',

        // Book Relationships (many-to-many)
        'Related (same)' => 'related_same_version',
        'Related (omnibus)' => 'related_omnibus',
        'Related (support)' => 'related_supporting',
        'Related (same title, different language, or similar)' => 'related_other_language',

        // ==========================================
        // FILE REFERENCES
        // ==========================================
        'DIGITAL SOURCE (WHERE IS THE PDF FROM)' => 'digital_source',
        'DOCUMENT FILENAME' => 'pdf_filename',
        'THUMBNAIL FILENAME' => 'thumbnail_filename',
        'ALTERNATIVE DOCUMENT FILENAME' => 'pdf_filename_alt',
        'ALTERNATIVE THUMBNAIL FILENAME' => 'thumbnail_filename_alt',
        'ALTERNATIVE DIGITAL SOURCE (WHERE IS THE PDF FROM)' => 'digital_source_alt',
        'Coupled audio' => 'audio_files',
        'Coupled video' => 'video_urls',

        // ==========================================
        // LIBRARY REFERENCES
        // ==========================================
        'UH hard copy ref' => 'uh_reference_number',
        'UH hard copy link' => 'uh_catalog_link',
        'UH hard copy call number' => 'uh_call_number',
        'UH note' => 'uh_notes',
        'COM hard copy ref' => 'com_reference_number',
        'COM hard copy call number' => 'com_call_number',
        'COM hard copy ref NOTE' => 'com_notes',

        // ==========================================
        // IGNORED/METADATA FIELDS
        // ==========================================
        'Name match check' => null, // Not stored
        'books.internal_id' => null, // Database mapping row - ignore
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Level Mapping
    |--------------------------------------------------------------------------
    |
    | Maps CSV values to database enum values
    |
    */
    'access_level_mapping' => [
        'Y' => 'full',
        'y' => 'full',
        'yes' => 'full',
        'YES' => 'full',
        'full' => 'full',
        'FULL' => 'full',

        'N' => 'unavailable',
        'n' => 'unavailable',
        'no' => 'unavailable',
        'NO' => 'unavailable',
        'unavailable' => 'unavailable',
        'UNAVAILABLE' => 'unavailable',

        'L' => 'limited',
        'l' => 'limited',
        'limited' => 'limited',
        'LIMITED' => 'limited',
    ],

    /*
    |--------------------------------------------------------------------------
    | Physical Type Mapping
    |--------------------------------------------------------------------------
    */
    'physical_type_mapping' => [
        'book' => 'book',
        'Book' => 'book',
        'BOOK' => 'book',
        'Booklet' => 'book',
        'booklet' => 'book',
        'BOOKLET' => 'book',
        'journal' => 'journal',
        'Journal' => 'journal',
        'JOURNAL' => 'journal',
        'magazine' => 'magazine',
        'Magazine' => 'magazine',
        'MAGAZINE' => 'magazine',
        'workbook' => 'workbook',
        'Workbook' => 'workbook',
        'WORKBOOK' => 'workbook',
        'poster' => 'poster',
        'Poster' => 'poster',
        'POSTER' => 'poster',
        'other' => 'other',
        'Other' => 'other',
        'OTHER' => 'other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Classification Type Mapping
    |--------------------------------------------------------------------------
    |
    | Maps CSV classification columns to classification_types slugs
    |
    */
    'classification_type_mapping' => [
        'classification_purpose' => 'purpose',
        'classification_genre' => 'genre',
        'classification_subgenre' => 'sub-genre',
        'classification_type' => 'type',
        'classification_themes' => 'themes-uses',
        'classification_learner_level' => 'learner-level',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Type Mapping
    |--------------------------------------------------------------------------
    */
    'relationship_type_mapping' => [
        'related_same_version' => 'same_version',
        'related_omnibus' => 'omnibus',
        'related_supporting' => 'supporting',
        'related_other_language' => 'other_language',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Paths (relative to storage/app/public/)
    |--------------------------------------------------------------------------
    */
    'file_paths' => [
        'pdf' => 'books/pdfs',
        'thumbnail' => 'books/thumbnails',
        'audio' => 'books/audio',
        'video' => 'books/video',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'required_fields' => ['title'], // ID can be auto-generated
        'unique_fields' => ['internal_id', 'palm_code'],

        'string_max_lengths' => [
            'internal_id' => 50,
            'palm_code' => 100,
            'title' => 500,
            'subtitle' => 500,
            'translated_title' => 500,
            'vla_standard' => 255,
            'vla_benchmark' => 255,
        ],

        'integer_ranges' => [
            'publication_year' => [1900, 2100],
            'pages' => [1, 10000],
        ],

        'enum_values' => [
            'physical_type' => ['book', 'journal', 'magazine', 'workbook', 'poster', 'other'],
            'access_level' => ['full', 'limited', 'unavailable'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'disable_model_events' => true, // Disable during bulk import
        'disable_foreign_key_checks' => false, // Careful with this
        'use_transactions' => true,
        'memory_limit' => '512M',
        'execution_time_limit' => 600, // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email_on_completion' => env('CSV_IMPORT_EMAIL_NOTIFICATIONS', false),
        'email_on_failure' => env('CSV_IMPORT_EMAIL_ON_FAILURE', true),
        'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'level' => env('CSV_IMPORT_LOG_LEVEL', 'info'),
        'retention_days' => 30,
    ],

];
