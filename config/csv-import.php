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
    |
    | batch_size: Number of rows to process in each batch
    | Lower values = slower but safer for memory/timeout
    | Higher values = faster but may hit memory/timeout limits
    | Recommended: 50-100 for large imports
    |
    */
    'batch_size' => env('CSV_IMPORT_BATCH_SIZE', 50),  // Reduced from 100 for safer large imports
    'chunk_size' => env('CSV_IMPORT_CHUNK_SIZE', 50),  // Reduced from 100

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
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Settings to optimize import performance for large files.
    | These options can significantly speed up bulk imports.
    |
    */
    'performance' => [
        // Enable database optimizations (disable foreign keys, query log)
        'enable_db_optimizations' => env('CSV_IMPORT_DB_OPTIMIZATIONS', true),

        // Disable foreign key checks during import (re-enabled after)
        'disable_foreign_keys' => env('CSV_IMPORT_DISABLE_FOREIGN_KEYS', true),

        // Track performance metrics (memory, speed, etc.)
        'track_performance' => env('CSV_IMPORT_TRACK_PERFORMANCE', true),

        // Log slow imports (imports slower than this threshold in seconds)
        'slow_import_threshold' => env('CSV_IMPORT_SLOW_THRESHOLD', 300), // 5 minutes

        // Memory limit warning threshold (MB)
        'memory_warning_threshold' => env('CSV_IMPORT_MEMORY_WARNING', 256),

        // Performance targets for monitoring
        'targets' => [
            'min_rows_per_second' => 10,
            'max_memory_mb' => 512,
            'max_duration_minutes' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup & Recovery
    |--------------------------------------------------------------------------
    |
    | Automatic database backup before CSV imports
    |
    */

    'backup' => [
        // Automatically create database backup before import
        'create_before_import' => env('CSV_IMPORT_CREATE_BACKUP', false),

        // Backup retention period in days
        'retention_days' => env('CSV_IMPORT_BACKUP_RETENTION', 30),

        // Enable automatic cleanup of old backups
        'auto_cleanup' => env('CSV_IMPORT_BACKUP_AUTO_CLEANUP', true),
    ],

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
        'Notes related to version.' => 'notes_version',  // NEW
        'Notes related to content.' => 'notes_content',
        'DESCRIPTION' => 'description',                  // NEW: Separate from abstract
        'ABSTRACT' => 'abstract',                        // NEW: Separate field
        'ABSTRACT/DESCRIPTION' => 'description',         // OLD: For backward compatibility
        'VLA standard' => 'vla_standard',
        'VLA benchmark' => 'vla_benchmark',
        'CONTACT' => 'contact',

        // ==========================================
        // ACCESS LEVEL (special mapping: Y/N/L → full/unavailable/limited)
        // ==========================================
        'LEVEL' => 'access_level',                      // Renamed from "UPLOADED"

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
        'Author' => 'author_1',                         // Now supports pipe separator
        'Other creator1' => 'other_creator_1',         // Renamed from "Other creator"
        'Other creator1 ROLE' => 'other_creator_1_role',  // Renamed from "Other creator ROLE"
        'Other creator2' => 'other_creator_2',
        'Other creator2 ROLE' => 'other_creator_2_role',
        'Other creator3' => 'other_creator_3',         // NEW
        'Other creator3 ROLE' => 'other_creator_3_role',  // NEW
        'Illustrator' => 'illustrator_1',               // Now supports pipe separator

        // Classifications (many-to-many)
        'Purpose' => 'classification_purpose',
        'Genre' => 'classification_genre',
        'Subject' => 'classification_subject',          // NEW: Subject classification
        'Sub-genre' => 'classification_subgenre',
        'Type' => 'classification_type',
        'Area' => 'classification_area',                // NEW: Area classification
        'Themes/Uses' => 'classification_themes',
        'Learner level' => 'classification_learner_level',

        // Keywords (one-to-many)
        'Keywords' => 'keywords',

        // Book Relationships (many-to-many)
        'Related (same)' => 'related_same_version',
        'Related (omnibus)' => 'related_omnibus',
        'Related (support)' => 'related_supporting',
        'Related (same title, different language, or similar)' => 'related_other_language',  // For backward compatibility

        // ==========================================
        // FILE REFERENCES
        // ==========================================
        'DIGITAL SOURCE (WHERE IS THE PDF FROM)' => 'digital_source',
        'DOCUMENT FILENAME' => 'pdf_filename',
        'THUMBNAIL FILENAME' => 'thumbnail_filename',
        'ALTERNATIVE DOCUMENT FILENAME' => 'pdf_filename_alt',
        'ALTERNATIVE THUMBNAIL FILENAME' => 'thumbnail_filename_alt',
        'ALTERNATIVE DIGITAL SOURCE (WHERE IS THE PDF FROM)' => 'digital_source_alt',
        'Uploaded audio' => 'audio_files',              // Renamed from "Coupled audio"
        'Uploaded video' => 'video_urls',               // Renamed from "Coupled video"
        'External video link' => 'video_urls',          // NEW: External video links
        'External web page link' => 'external_link',    // NEW: External web page links

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

        // NEW: Library Links (6 libraries × 2 links each = 12 fields)
        // Support both old and new CSV formats
        'Library link UH' => 'library_link_uh',                     // Old CSV format
        'Library link UH alt.' => 'library_link_uh_alt',            // Old CSV format
        'Library UH link' => 'library_link_uh',                     // New CSV format (production)
        'Library UH comment' => 'library_link_uh_alt',              // New CSV format (production)

        'Library link COM-FSM' => 'library_link_com_fsm',           // Old CSV format
        'Library link COM-FSM alt.' => 'library_link_com_fsm_alt',  // Old CSV format
        'Library COM link' => 'library_link_com_fsm',               // New CSV format (production)
        'Library COM comment' => 'library_link_com_fsm_alt',        // New CSV format (production)

        'Library link UOG' => 'library_link_uog',                   // Old CSV format
        'Library link UOG alt.' => 'library_link_uog_alt',          // Old CSV format
        'Library UOG link' => 'library_link_uog',                   // New CSV format (production)
        'Library UOG comment' => 'library_link_uog_alt',            // New CSV format (production)

        'Library link MICSEM' => 'library_link_micsem',             // Old CSV format
        'Library link MICSEM alt.' => 'library_link_micsem_alt',    // Old CSV format
        'Library MICSEM link' => 'library_link_micsem',             // New CSV format (production)
        'Library MICSEM comment' => 'library_link_micsem_alt',      // New CSV format (production)

        'Library link MARC' => 'library_link_marc',                 // Old CSV format
        'Library link MARC alt.' => 'library_link_marc_alt',        // Old CSV format

        // ==========================================
        // EXTERNAL LINKS
        // ==========================================
        'External link' => 'external_link',                        // NEW: General external link
        'External web page link' => 'external_link',               // NEW: Web page link variant

        // ==========================================
        // BOOK IDENTIFIERS (NEW)
        // ==========================================
        'OLLC number' => 'oclc_number',      // Column BR
        'ISBN number' => 'isbn_number',      // Column BS
        'Other number' => 'other_number',    // Column BT
        'COM call number' => 'com_call_number',  // NEW: Library call number

        // ==========================================
        // IGNORED/METADATA FIELDS (Not stored in database)
        // ==========================================
        // Note: "Related (translated)" and "Name match check" have been REMOVED
        'books.internal_id' => null,          // Database mapping row - ignore
        'Verified' => null,                   // Verification flag - not stored
        'Notes' => null,                      // General notes column - not stored
        'Comments' => null,                   // Comments column - not stored
        'Status' => null,                     // Status indicator - not stored
        'Last updated' => null,               // Timestamp - not stored
        'Created by' => null,                 // Creator info - not stored
        'Record ID' => null,                  // Internal record reference - not stored
        'Batch number' => null,               // Batch tracking - not stored
        'Import date' => null,                // Import timestamp - not stored
        'Source' => null,                     // Data source - not stored
        'Quality check' => null,              // QA flag - not stored
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
        // Full Access (Y or T = Temporary/Full quality)
        'Y' => 'full',
        'y' => 'full',
        'T' => 'full',                      // NEW: Temporary - same as full until better scan available
        't' => 'full',
        'yes' => 'full',
        'YES' => 'full',
        'full' => 'full',
        'FULL' => 'full',

        // Restricted Access
        'R' => 'limited',                   // NEW: Restricted - same as limited
        'r' => 'limited',
        'restricted' => 'limited',
        'RESTRICTED' => 'limited',

        // Unavailable
        'N' => 'unavailable',
        'n' => 'unavailable',
        'no' => 'unavailable',
        'NO' => 'unavailable',
        'unavailable' => 'unavailable',
        'UNAVAILABLE' => 'unavailable',

        // Limited Access
        'L' => 'limited',
        'l' => 'limited',
        'limited' => 'limited',
        'LIMITED' => 'limited',
    ],

    /*
    |--------------------------------------------------------------------------
    | Physical Type Mapping
    |--------------------------------------------------------------------------
    |
    | REMOVED: Physical types are now dynamically created from CSV.
    | Any physical type value from CSV will automatically create a new
    | PhysicalType record if it doesn't already exist.
    |
    */

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
        'classification_subject' => 'subject',          // NEW
        'classification_subgenre' => 'sub-genre',
        'classification_type' => 'type',
        'classification_area' => 'area',                // NEW
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
        'related_other_language' => 'other_language', // For backward compatibility
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Paths (relative to storage/app/public/)
    |--------------------------------------------------------------------------
    */
    'file_paths' => [
        'pdf' => 'books',
        'thumbnail' => 'books',
        'audio' => 'books',
        'video' => 'books',
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
            // physical_type removed - now uses dynamic physical_types table
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
