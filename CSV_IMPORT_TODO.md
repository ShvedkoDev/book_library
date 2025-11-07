# CSV Import/Export System - TODO

## Overview
Complete CSV import/export system for managing the library's book database, enabling initial bulk upload of 1000+ books and ongoing maintenance through external CSV editing.

---

## 🎯 COMPLETION STATUS

### ✅ Phase 1: CSV Field Mapping & Templates (COMPLETED - 2025-11-06)

**Completed Tasks**:
- ✅ **Section 1.1**: Define CSV Column Headers - ALL 65+ fields documented
- ✅ **Section 1.2**: Create CSV Templates (manual) - 2 templates + README created
- ✅ **Section 9.1**: User Documentation - Comprehensive guides created
- ✅ **Section 12.1**: Storage directories for templates created

**Deliverables**:
1. `/docs/CSV_FIELD_MAPPING.md` - Complete field reference (1300+ lines)
2. `/docs/CSV_QUICK_REFERENCE.md` - Quick reference guide
3. `/storage/csv-templates/book-import-template.csv` - Blank template with 65+ columns
4. `/storage/csv-templates/book-import-example.csv` - 3 example books
5. `/storage/csv-templates/README.md` - Template usage guide

**Key Features**:
- Based on existing `test.csv` structure with 1000+ books
- Two-row header system (readable + database mapping)
- Pipe-separated multi-value fields
- All relationship types documented
- UTF-8 with special character support
- Validation rules and error handling guidelines

---

### ✅ Phase 2: CSV Import System (COMPLETED - 2025-11-07)

**Completed Tasks**:
- ✅ **Section 2.1**: Core Import Service - Full implementation
- ✅ **Section 2.2**: Import Validation System - Complete validation logic
- ✅ **Section 2.3**: Duplicate Detection - By internal_id and palm_code
- ✅ **Section 2.4**: Relationship Resolution - All 9 relationship types
- ✅ **Section 2.5**: Error Handling & Reporting - Row-level error tracking
- ✅ **Section 2.6**: Progress Tracking & Background Processing - Queue job implemented
- ✅ **Section 2.7**: Initial Bulk Upload Process - Tools & documentation ready
- ✅ **Section 12.1**: CSV Imports Migration - Database table created
- ✅ **Section 12.1**: Storage Directories - All directories created

**Deliverables**:
1. `/config/csv-import.php` - Complete configuration with 65+ field mappings
2. `/app/Services/BookCsvImportService.php` - Main import service (630+ lines)
3. `/app/Services/BookCsvImportValidation.php` - Validation trait
4. `/app/Services/BookCsvImportRelationships.php` - Relationship resolution trait
5. `/app/Models/CsvImport.php` - Import session tracking model
6. `/database/migrations/..._create_csv_imports_table.php` - Migration
7. `/app/Console/Commands/ImportBooksFromCsv.php` - CLI import command
8. `/app/Jobs/ImportBooksFromCsvJob.php` - Background queue job
9. `/app/Console/Commands/CheckImportPrerequisites.php` - Prerequisites checker
10. `/docs/BULK_UPLOAD_GUIDE.md` - Comprehensive bulk upload guide
11. `/storage/csv-imports/`, `/storage/csv-exports/`, `/storage/logs/csv-imports/` - Directories

**Key Features Implemented**:
- ✅ CSV parsing with PHP native functions (no external dependencies)
- ✅ Two-row header detection (readable + database mapping)
- ✅ Batch processing (100 rows per batch)
- ✅ Transaction support with rollback
- ✅ Import session tracking in database
- ✅ Background processing with Laravel queues
- ✅ Progress tracking and cancellation support
- ✅ 4 import modes (create_only, update_only, upsert, create_duplicates)
- ✅ Comprehensive validation (structure, data types, enums, ranges)
- ✅ Duplicate detection by internal_id and palm_code
- ✅ Automatic relationship resolution for:
  - Collections & Publishers (create if missing)
  - Languages (by name or ISO code)
  - Creators (Authors, Illustrators, Editors, Others with roles)
  - Classifications (6 types: Purpose, Genre, Sub-genre, Type, Themes, Learner Level)
  - Geographic Locations (Islands, States)
  - Keywords (pipe-separated)
  - Files (PDF, Thumbnails, Audio, Video)
  - Library References (UH, COM)
  - Book Relationships (4 types: same_version, omnibus, supporting, other_language)
- ✅ Error tracking with row numbers and specific messages
- ✅ Progress tracking (processed, successful, failed, skipped counts)
- ✅ CLI command with validation-only mode
- ✅ Access level mapping (Y/N/L → full/unavailable/limited)
- ✅ Physical type normalization
- ✅ Year cleaning (removes question marks)

---

### ✅ Phase 3: CSV Export System (COMPLETED - 2025-11-07)

**Completed Tasks**:
- ✅ **Section 3.1**: Core Export Service - Full implementation
- ✅ **Section 3.2**: Export Filters & Options - All basic filters implemented
- ✅ **Section 3.3**: Relationship Flattening - All 9 relationship types
- ✅ **Section 3.4**: Export Formats - CSV and TSV formats complete

**Deliverables**:
1. `/app/Services/BookCsvExportService.php` - Complete export service (~600 lines)
2. `/app/Console/Commands/ExportBooksToCsv.php` - CLI export command

**Key Features Implemented**:
- ✅ Export all books or filtered subsets
- ✅ Multiple export formats:
  - CSV (comma-delimited with optional BOM)
  - TSV (tab-delimited, better for texts with commas)
- ✅ Comprehensive filtering system:
  - Date ranges (created_at, updated_at)
  - Access level (single or multiple)
  - Collection (single or multiple)
  - Language (single or multiple)
  - Publication year range
  - Active/Featured status
- ✅ Complete relationship flattening:
  - Primary/Secondary languages with ISO codes
  - Authors (1-3), Illustrators (1-5) by position
  - Other creators with roles
  - All 6 classification types (pipe-separated)
  - Geographic locations (pipe-separated)
  - Keywords (pipe-separated)
  - File references (PDF, thumbnails, audio, video)
  - Library references (UH, COM with all fields)
  - Book relationships (4 types with internal IDs)
- ✅ Reverse mappings (full/unavailable/limited → Y/N/L)
- ✅ UTF-8 with BOM for Excel compatibility (CSV only)
- ✅ Proper delimiter handling (comma vs tab)
- ✅ Two-row headers (readable + database mapping)
- ✅ Chunked processing for memory efficiency
- ✅ CLI command with extensive filter options and format validation

**Next Steps**: Section 4 complete with preview functionality. Proceed to Section 6 (Filament UI) when needed.

---

### ✅ Phase 4: Re-import of Edited CSV (COMPLETED - 2025-11-07)

**Completed Tasks**:
- ✅ **Section 4.1**: Change Detection System - Preview mode with field-level diff
- ✅ **Section 4.2**: Update Strategy - Already implemented in Phase 2
- ✅ **Section 5.1**: Adding New Books - Supported by existing commands
- ✅ **Section 5.2**: Batch Update Process - Fully operational workflows

**Deliverables**:
1. `/app/Services/BookCsvImportService::previewCsv()` - Change preview method
2. `/app/Services/BookCsvImportService::detectChanges()` - Field comparison
3. `/app/Console/Commands/ImportBooksFromCsv` - Enhanced with `--preview` flag

**Key Features Implemented**:
- ✅ Change preview mode (dry-run with diff):
  - Shows how many books will be created/updated/skipped
  - Displays field-level changes (old → new values)
  - Respects import mode (create_only, update_only, upsert)
  - Sample output of first 10 changes
  - No data modifications in preview mode
- ✅ Update strategy (already operational):
  - Matches by internal_id then palm_code
  - Four import modes for different scenarios
  - Relationship replacement on update
  - Skip behavior for non-matching records
- ✅ Re-import workflows:
  - Export → Edit → Preview → Import cycle
  - Filtered exports for targeted updates
  - Mode-specific imports (create_only, update_only)
  - Comprehensive examples and documentation

**Deferred Items**:
- Section 4.3 (Audit Trail): Field-level change history - future enhancement
- Section 4.4 (Rollback): Snapshot-based rollback - future enhancement

**Command Usage**:
```bash
# Complete re-import workflow
php artisan books:export-csv --output=books.csv
# Edit books.csv
php artisan books:import-csv books.csv --preview --mode=upsert
php artisan books:import-csv books.csv --mode=upsert

# Filtered batch update
php artisan books:export-csv --collection=1 --output=collection1.csv
# Edit access levels
php artisan books:import-csv collection1.csv --preview --mode=update_only
php artisan books:import-csv collection1.csv --mode=update_only
```

**Next Steps**: All core functionality complete. Optional sections remain for testing, optimization, and additional features.

---

### ✅ Phase 5: Filament Admin Interface (COMPLETED - 2025-11-07)

**Completed Tasks**:
- ✅ **Section 6.1**: CSV Import Page - Full web-based import UI
- ✅ **Section 6.2**: CSV Export Page - Full web-based export UI with filters
- ✅ **Section 6.3**: Import History Resource - Complete import tracking
- ✅ **Section 6.4**: CSV Template Download - Template and export downloads

**Deliverables**:
1. `/app/Filament/Resources/CsvImportResource.php` - Import history resource
2. `/app/Filament/Resources/CsvImportResource/Pages/ListCsvImports.php` - History list
3. `/app/Filament/Resources/CsvImportResource/Pages/ViewCsvImport.php` - Import details
4. `/app/Filament/Pages/CsvImport.php` - Web-based import page
5. `/app/Filament/Pages/CsvExport.php` - Web-based export page
6. `/resources/views/filament/pages/csv-import.blade.php` - Import view
7. `/resources/views/filament/pages/csv-export.blade.php` - Export view
8. Routes in `/routes/web.php` - Template and export downloads

**Key Features Implemented**:
- ✅ **CSV Import Page** (`/admin/csv-import`):
  - File upload field (CSV/TXT, max 50MB)
  - Import mode selection (upsert, create_only, update_only, create_duplicates)
  - Configuration options (create missing relations, skip invalid rows)
  - Validate Only button (dry-run)
  - Import CSV button with confirmation
  - Template download links (blank and example)
  - Comprehensive help section
  - Success/error notifications
  - Link to view import details

- ✅ **CSV Export Page** (`/admin/csv-export`):
  - Format selection (CSV/TSV)
  - BOM option (CSV only)
  - Database mapping row option
  - Comprehensive filters:
    - Collection (searchable)
    - Language (searchable)
    - Access level
    - Active/inactive status
    - Created date range
    - Publication year range
    - Featured books only
  - Chunk size configuration
  - Export button with immediate execution
  - Download notification with file size
  - 24-hour file expiration
  - Help section with use cases

- ✅ **Import History Resource** (`/admin/csv-imports`):
  - Comprehensive table with all import details
  - Filters (status, mode, has errors, recent)
  - Sortable columns
  - Detailed view page with:
    - Import summary
    - Statistics with color coding
    - Success rate indicator
    - Timing information
    - Error log (first 20 errors)
  - Delete action for cleanup
  - Links to import/export pages

- ✅ **Template & File Downloads**:
  - Blank template download
  - Example template download
  - Export file download with auto-expiration
  - Secure authenticated routes

**Admin Access**:
All features accessible through Filament admin panel under "CSV Import/Export" navigation group:
- CSV Import (sort order: 1)
- CSV Export (sort order: 2)
- Import History (sort order: 3)

**Next Steps**: Section 7 (Validation & Data Quality) - optional enhancements

---

## 1. CSV FIELD MAPPING & DATA STRUCTURE

### 1.1 Define CSV Column Headers ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Create comprehensive CSV template with all supported columns
- [x] Map CSV headers to database fields (books table)
- [x] Define required vs optional columns
- [x] Document column format specifications (date formats, enums, etc.)

**Deliverables**:
- ✅ `/docs/CSV_FIELD_MAPPING.md` - Complete 65+ field documentation
- ✅ `/docs/CSV_QUICK_REFERENCE.md` - Quick reference guide
- ✅ `/storage/csv-templates/book-import-template.csv` - Blank template
- ✅ `/storage/csv-templates/book-import-example.csv` - Example with 3 books
- ✅ `/storage/csv-templates/README.md` - Template usage guide

#### Core Book Fields (Direct Mapping)
```csv
ID, PALM_code, Title, Sub-title, Translated-title, Physical_type,
Collection, Publisher, Year, Pages, Description, TOC, Notes_issue,
Notes_content, Contact, Access_level, VLA_standard, VLA_benchmark,
Is_featured, Is_active, Sort_order
```

#### Relational Fields (Many-to-Many)
- [x] **Languages**: Primary_language, Additional_languages (pipe-separated)
- [x] **Creators**:
  - Authors (pipe-separated with optional role)
  - Illustrators (pipe-separated)
  - Editors (pipe-separated)
  - Other_creators (pipe-separated with role)
- [x] **Classifications**:
  - Purpose (pipe-separated classification values)
  - Genre (pipe-separated)
  - Sub_genre (pipe-separated)
  - Type (pipe-separated)
  - Themes_uses (pipe-separated)
  - Learner_level (pipe-separated)
- [x] **Geographic Locations**: Locations (pipe-separated)
- [x] **Keywords**: Keywords (pipe-separated)

#### File References
- [x] **Files**:
  - PDF_filename (path or filename)
  - Thumbnail_filename (path or filename)
  - Audio_files (pipe-separated filenames)
  - Video_files (pipe-separated filenames)

#### Related Books
- [x] **Book Relationships**:
  - Same_version (pipe-separated internal IDs)
  - Same_language (pipe-separated internal IDs)
  - Supporting_materials (pipe-separated internal IDs)
  - Other_language (pipe-separated internal IDs)

### 1.2 Create CSV Template Generator ✅ PARTIALLY COMPLETED
**Priority: HIGH** | **Complexity: LOW**

- [ ] Build artisan command: `php artisan csv:generate-template` *(Future enhancement)*
- [x] Output blank CSV with all column headers
- [x] Include commented row with field descriptions (Row 2: database field mapping)
- [x] Include example row with sample data (3 example books in separate file)
- [x] Save to `/storage/csv-templates/book-import-template.csv`

**Note**: Templates created manually. Artisan command can be added later for dynamic generation.

---

## 2. CSV IMPORT SYSTEM

### 2.1 Core Import Service ✅ COMPLETED
**Priority: HIGH** | **Complexity: HIGH**

- [x] Create `App\Services\BookCsvImportService` class
- [x] Implement CSV parsing with PHP native functions (no external libraries needed)
- [x] Build field mapper (CSV columns → database fields)
- [x] Implement batch processing for large files (chunk size: 100 rows)
- [x] Add transaction support (rollback on critical errors)
- [x] Track import session (timestamps, user, file info)

#### Key Methods to Implement
```php
- validateCsv($filePath): array
- importCsv($filePath, $options = []): ImportResult
- processBatch($rows): void
- mapCsvRowToBook($row): array
- resolveRelationships($row): array
- handleErrors($errors): void
```

### 2.2 Import Validation System ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Validate CSV structure (headers match expected columns)
- [x] Validate required fields (title, internal_id if updating)
- [x] Validate data types (integers, dates, enums)
- [x] Validate enum values (physical_type, access_level)
- [x] Validate foreign key references (collection, publisher, languages)
- [x] Check for duplicate internal_ids
- [x] Check for duplicate palm_codes
- [ ] Validate file references (check if files exist) *(Deferred - files validated during import)*
- [x] Create validation report with line numbers and specific errors

#### Validation Rules
```php
- internal_id: unique, max:50
- palm_code: unique, max:100
- title: required, max:500
- publication_year: integer, between:1900,2100
- pages: integer, min:1
- access_level: in:full,limited,unavailable
- physical_type: in:book,journal,magazine,workbook,poster,other
```

### 2.3 Duplicate Detection & Conflict Resolution ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Implement duplicate detection strategies:
  - By `internal_id` (primary)
  - By `palm_code` (secondary)
  - ~~By title + publication_year (fuzzy match)~~ *(Not implemented - exact match only)*
- [x] Create import mode options:
  - `create_only`: Skip existing records
  - `update_only`: Only update existing records
  - `upsert`: Create new or update existing (default)
  - `create_duplicates`: Allow duplicates with new IDs
- [ ] Build conflict resolution interface (show differences) *(Deferred to Filament UI)*
- [x] Allow user to choose resolution strategy before import (via CLI --mode flag)

### 2.4 Relationship Resolution ✅ COMPLETED
**Priority: HIGH** | **Complexity: HIGH**

#### Collection & Publisher Resolution ✅
- [x] Lookup by name (exact match, case-insensitive)
- [x] Create new if `create_missing_relations` option enabled
- [x] Report unresolved references

#### Language Resolution ✅
- [x] Lookup languages by code or name
- [x] Handle multiple languages (pipe-separated)
- [x] Set primary language flag
- [x] Create book_languages pivot records

#### Creator Resolution (Authors, Illustrators, Editors) ✅
- [x] Parse creator names from CSV
- [x] Lookup or create Creator records
- [x] Determine creator_type (author, illustrator, editor, other)
- [x] Parse optional role_description
- [x] Set sort_order based on CSV order
- [x] Create book_creators pivot records
- [x] **Bonus**: Auto-detect creator type from role (translator, compiler, adapter, etc.)

#### Classification Resolution ✅
- [x] Lookup ClassificationValue by label and type
- [x] Handle multiple values per classification type
- [x] Create book_classifications pivot records
- [x] Map CSV columns to classification types:
  - Purpose → 'purpose'
  - Genre → 'genre'
  - Sub_genre → 'sub-genre'
  - Type → 'type'
  - Themes_uses → 'themes-uses'
  - Learner_level → 'learner-level'

#### Geographic Location Resolution ✅
- [x] Lookup GeographicLocation by name
- [x] Create book_locations pivot records

#### Keyword Processing ✅
- [x] Parse keywords (pipe-separated)
- [x] Create BookKeyword records

#### File Association ✅
- [x] Validate file paths exist
- [x] Create BookFile records
- [x] Set primary flags for main PDF and thumbnail
- [x] Handle multiple audio/video files
- [x] **Bonus**: Auto-construct file paths and detect MIME types

#### Book Relationships ✅
- [x] Resolve related book IDs (by internal_id)
- [x] Create BookRelationship records
- [ ] Handle bidirectional relationships if needed *(Deferred - create manually if needed)*

### 2.5 Error Handling & Reporting ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Collect all errors during import (don't stop on first error)
- [x] Create detailed error report:
  - Row number
  - Column name
  - Error type (validation, missing reference, file not found)
  - Error message
  - ~~Suggested fix~~ *(Not implemented - manual review needed)*
- [x] Generate import summary:
  - Total rows processed
  - Successfully imported
  - Updated records
  - Failed records
  - Warnings (skipped rows)
- [x] Save error log to database (csv_imports table)
- [ ] Email error report to admin if requested *(Deferred to notification system)*

### 2.6 Progress Tracking & Background Processing ✅ COMPLETED
**Priority: MEDIUM** | **Complexity: MEDIUM**

- [x] Implement queue job: `ImportBooksFromCsv` ✅
- [x] Add progress tracking using Laravel queues ✅
- [x] Store import status in database (csv_imports table) ✅
- [ ] Real-time progress updates via Livewire polling or websockets *(Deferred to Filament UI)*
- [x] Allow cancellation of in-progress imports ✅ (Infrastructure ready with cancelImport method)
- [ ] Clean up failed imports (optional rollback) *(Deferred - future enhancement)*

**Deliverables**:
- ✅ `/app/Jobs/ImportBooksFromCsvJob.php` - Queue job with 3 retries, 1-hour timeout
- ✅ Updated `BookCsvImportService` with async methods:
  - `importCsvAsync()` - Dispatch job to queue
  - `getImportProgress()` - Check real-time progress
  - `cancelImport()` - Cancel in-progress import
  - `setImportSession()` - Connect job to import session

**Note**: Complete background processing infrastructure in place. Queue worker handles large imports with automatic retries, progress tracking, and error handling.

### 2.7 Initial Bulk Upload Process ✅ TOOLS & DOCUMENTATION COMPLETED
**Priority: HIGH** | **Complexity: HIGH**

**Status**: Tools and documentation completed. Actual execution pending production data availability.

#### Pre-Import Checklist Tools ✅
- [x] Created prerequisite checker command: `php artisan books:check-prerequisites`
  - Verifies all related data exists (Collections, Publishers, Languages, etc.)
  - Checks storage directories exist and are writable
  - Validates configuration settings
  - Checks queue worker status
  - Can check specific CSV file structure
  - Provides detailed pass/warning/fail report
- [x] Created comprehensive bulk upload guide: `/docs/BULK_UPLOAD_GUIDE.md`
  - Step-by-step instructions for entire process
  - Prerequisites checklist with commands
  - CSV preparation guidelines
  - Validation workflow
  - Import execution with all modes explained
  - Troubleshooting guide
  - Post-import verification steps
  - Re-import and update processes
  - Best practices and performance expectations

#### Pre-Import Checklist (Manual Tasks - Awaiting Production Data)
- [ ] Ensure all related data exists *(Use: `php artisan books:check-prerequisites --detailed`)*:
  - Collections
  - Publishers
  - Languages *(REQUIRED - cannot be auto-created)*
  - Classification Types & Values *(6 types required)*
  - Creators (or enable auto-creation)
  - Geographic Locations
- [ ] Upload all PDF files to `/storage/app/public/books/pdfs/`
- [ ] Upload all thumbnail images to `/storage/app/public/books/thumbnails/`
- [ ] Prepare CSV file with 1000+ book records *(Use template from `/storage/csv-templates/`)*

#### Import Configuration for Initial Upload ✅
Recommended command:
```bash
php artisan books:import-csv /path/to/books.csv \
  --mode=upsert \
  --create-missing \
  --skip-invalid
```

Alternative configuration via options array:
```php
[
    'mode' => 'upsert',
    'create_missing_relations' => true,
    'validate_file_references' => true,
    'skip_invalid_rows' => true,  // Skip rows with errors, continue import
]
```

#### Execution Steps (Documented - Awaiting Production Data)
- [x] Documentation: Validation-only pass workflow
  - Command: `php artisan books:import-csv <file> --validate-only`
  - Validation report interpretation
- [x] Documentation: Error review and fixing process
  - Common validation errors
  - How to fix issues in CSV
- [x] Documentation: Actual import execution
  - Command: `php artisan books:import-csv <file> --mode=upsert --create-missing`
  - Progress monitoring
- [x] Documentation: Import summary review
  - Success metrics
  - Error log analysis
- [x] Documentation: Data integrity verification
  - Database queries for spot checks
  - Admin panel verification
  - Frontend testing
- [ ] *Actual execution pending production CSV file with 1000+ books*

**Deliverables**:
- ✅ `/app/Console/Commands/CheckImportPrerequisites.php` - Comprehensive prerequisites checker
- ✅ `/docs/BULK_UPLOAD_GUIDE.md` - Complete bulk upload guide (40+ pages)

**Command Usage**:
```bash
# Check all prerequisites
php artisan books:check-prerequisites --detailed

# Check specific CSV file
php artisan books:check-prerequisites --csv-file=/path/to/books.csv

# Validate CSV before import
php artisan books:import-csv /path/to/books.csv --validate-only

# Execute bulk import
php artisan books:import-csv /path/to/books.csv --mode=upsert --create-missing
```

**Note**: All tools and documentation are complete and ready for use. Actual bulk upload execution requires production CSV file and PDF files to be provided.

---

## 3. CSV EXPORT SYSTEM

### 3.1 Core Export Service ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Create `App\Services\BookCsvExportService` class ✅
- [x] Export all books or filtered subset ✅
- [x] Include all fields (match import format) ✅
- [x] Flatten relationships (pipe-separated values) ✅
- [x] Handle special characters (proper CSV escaping) ✅
- [x] Set UTF-8 BOM for Excel compatibility ✅

**Deliverables**:
- ✅ `/app/Services/BookCsvExportService.php` - Complete export service (~600 lines)
- ✅ `/app/Console/Commands/ExportBooksToCsv.php` - CLI export command

#### Key Methods Implemented ✅
```php
✅ exportAll($options = []): string
✅ export(Builder $query, array $options = []): string
✅ exportFiltered(array $filters, array $options = []): string
✅ formatBookForCsv(Book $book, array $options): array
✅ getFieldValue(Book $book, string $fieldName, array $options): string
✅ generateFilename(array $options): string
✅ applyFilters(Builder $query, array $options): Builder
```

### 3.2 Export Filters & Options ✅ PARTIALLY COMPLETED
**Priority: MEDIUM** | **Complexity: LOW**

- [x] Filter by date range (created_at, updated_at) ✅
- [x] Filter by access_level ✅
- [x] Filter by collection ✅
- [x] Filter by language ✅
- [x] Filter by is_active, is_featured ✅
- [x] Filter by publication year range ✅
- [ ] Export only specific columns (custom field selection) *(Future enhancement)*
- [ ] Option to include/exclude:
  - Analytics data (view_count, download_count) *(Future enhancement)*
  - User-generated content (ratings, reviews) *(Future enhancement)*
  - System fields (created_at, updated_at, id) *(Future enhancement)*

**Implemented Filters**:
- Date ranges: `created_from`, `created_to`, `updated_from`, `updated_to`
- Access level: Single or array of levels
- Collection ID: Single or array of collections
- Language ID: Single or array of languages (via relationship)
- Publication year: `year_from`, `year_to`
- Boolean flags: `is_active`, `is_featured`

### 3.3 Relationship Flattening ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

#### Multi-value Field Formatting ✅
- [x] Languages: Primary and secondary languages exported separately ✅
- [x] Authors: `"Author 1"`, `"Author 2"`, `"Author 3"` (indexed fields) ✅
- [x] Illustrators: `"Illustrator 1"` through `"Illustrator 5"` (indexed fields) ✅
- [x] Other Creators: With role descriptions in separate columns ✅
- [x] Classifications: Pipe-separated by type (6 types) ✅
- [x] Keywords: `"education|teaching|pacific islands"` ✅
- [x] Geographic Locations: Pipe-separated ✅
- [x] Related Books: Pipe-separated internal IDs by relationship type ✅

#### Special Cases ✅
- [x] Creators with roles: Role stored in `other_creator_1_role` column ✅
- [x] Creator type detection: Automatic type inference from role (translator, editor, etc.) ✅
- [x] Primary/Secondary languages: Separate columns with ISO codes ✅
- [x] File paths: Full paths with filename and extension ✅
- [x] Access level reverse mapping: full/unavailable/limited → Y/N/L ✅
- [x] Multi-value separator: Configurable pipe separator ✅

**Implemented Relationship Flattening**:
```php
✅ getCreatorByIndex() - Authors/Illustrators by position (1-5)
✅ getOtherCreator() - Other creators with roles
✅ getCreatorRole() - Role descriptions for other creators
✅ getClassificationValues() - All 6 classification types
✅ getLibraryReference() - UH and COM references with all fields
✅ getRelatedBooks() - All 4 relationship types
✅ joinMultiValue() - Pipe-separated multi-value fields
```

### 3.4 Export Formats ✅ PARTIALLY COMPLETED
**Priority: LOW** | **Complexity: LOW**

- [x] CSV (default): Standard comma-separated ✅
- [x] TSV: Tab-separated (better for texts with commas) ✅
- [ ] Excel: Generate .xlsx file with formatting *(Future enhancement)*
- [ ] JSON: For programmatic use *(Future enhancement)*

**Current Implementation**:
- ✅ CSV format fully implemented with proper escaping
- ✅ TSV format fully implemented with tab delimiter
- ✅ UTF-8 encoding with optional BOM for Excel compatibility (CSV only)
- ✅ Proper field delimiter handling (comma for CSV, tab for TSV)
- ✅ Multi-value separator remains pipe (|) for both formats
- ✅ Two-row header system (readable + database mapping)

**Format Details**:
- **CSV**: Comma-delimited fields, optional UTF-8 BOM, proper quote escaping
- **TSV**: Tab-delimited fields, no BOM, better for texts containing commas

**Command Usage**:
```bash
# Export as CSV (default)
php artisan books:export-csv
  [--output=/path/to/file.csv]
  [--format=csv]
  [--access-level=full]
  [--collection=1]
  [--language=2]
  [--created-from=2024-01-01]
  [--year-from=2000]
  [--no-bom]
  [--chunk-size=100]

# Export as TSV
php artisan books:export-csv --format=tsv --output=books.tsv
```

---

## 4. RE-IMPORT OF EDITED CSV

### 4.1 Change Detection System ✅ COMPLETED
**Priority: HIGH** | **Complexity: HIGH**

- [x] Compare imported CSV with existing database records ✅
- [x] Detect changes in core book fields ✅
- [x] Generate change preview report ✅
- [x] Show before/after comparison for each changed field ✅
- [ ] Detect changes in relationships (added/removed) *(Deferred - complex feature)*
- [ ] Require admin approval before applying changes *(Supported via preview mode)*

**Deliverables**:
- ✅ `/app/Services/BookCsvImportService::previewCsv()` - Preview changes without importing
- ✅ `/app/Services/BookCsvImportService::detectChanges()` - Field-level change detection
- ✅ `/app/Console/Commands/ImportBooksFromCsv` - Added `--preview` flag

**Command Usage**:
```bash
# Preview changes before importing (dry-run with change detection)
php artisan books:import-csv /path/to/edited-books.csv --preview --mode=upsert

# Shows:
# - How many books will be created
# - How many books will be updated
# - How many will be skipped
# - Sample of field-level changes (old vs new values)
```

**Features Implemented**:
- Analyzes CSV without making changes
- Shows action for each record (create/update/skip)
- Displays field-level differences (old → new)
- Respects import mode (create_only, update_only, upsert)
- Shows first 10 samples of creates and updates
- Provides summary statistics

### 4.2 Update Strategy ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Match records by `internal_id` (primary) ✅
- [x] Match by `palm_code` (secondary) ✅
- [ ] Match by exact title (tertiary) *(Not implemented - could cause false matches)*
- [x] Handle missing records with skip behavior ✅
- [x] Create as new record if option enabled (upsert mode) ✅
- [x] Handle relationship updates (replace strategy) ✅
- [ ] Merge strategy for relationships *(Future enhancement)*

**Already Implemented Features**:
- ✅ `findExistingBook()` method matches by internal_id then palm_code
- ✅ Four import modes:
  - `create_only`: Only create new books, skip existing
  - `update_only`: Only update existing books, skip new
  - `upsert`: Create new or update existing (default for re-import)
  - `create_duplicates`: Allow duplicates with new IDs
- ✅ Relationship handling:
  - All relationships are replaced during update (not merged)
  - Old relationships are detached, new ones attached
  - Works for all 9 relationship types

**Re-import Workflow**:
1. Export current data: `php artisan books:export-csv --output=current-books.csv`
2. Edit CSV file with desired changes
3. Preview changes: `php artisan books:import-csv edited-books.csv --preview`
4. Review preview output carefully
5. Import updates: `php artisan books:import-csv edited-books.csv --mode=upsert`

**Note**: Update strategy has been fully operational since Phase 2. Section 4.1 adds preview capability to see changes before applying.

### 4.3 Audit Trail ⏸️ DEFERRED
**Priority: MEDIUM** | **Complexity: MEDIUM**

- [ ] Create `book_updates` table to track all changes *(Future enhancement)*
- [ ] Record change history:
  - Book ID
  - Updated by (user_id)
  - Update source (csv_import)
  - Changed fields (JSON)
  - Old values (JSON)
  - New values (JSON)
  - Timestamp
- [ ] Add `updated_by` foreign key to books table *(Future enhancement)*
- [ ] Display update history in admin panel *(Future enhancement)*

**Current Tracking**:
- ✅ CsvImport model tracks import sessions
- ✅ Import statistics (created, updated, failed counts)
- ✅ Error logs with row numbers
- ✅ User ID and timestamps for each import
- ✅ Preview mode shows what would change

**Note**: Field-level change audit trail deferred to future phase. Current tracking provides import-level auditing which is sufficient for most use cases.

### 4.4 Rollback Capability ⏸️ DEFERRED
**Priority: LOW** | **Complexity: HIGH**

- [ ] Store snapshot of database state before import *(Future enhancement)*
- [ ] Allow rollback to pre-import state *(Future enhancement)*
- [ ] Implement undo last import functionality *(Future enhancement)*
- [ ] Time limit on rollback (24 hours?) *(Future enhancement)*

**Current Workaround**:
- Use `--preview` mode to verify changes before importing
- Export current state before major updates as backup
- Database backups provide rollback capability at infrastructure level

**Note**: Rollback feature deferred due to complexity. Preview mode reduces need for rollback by allowing verification before changes.

---

## 5. INCREMENTAL ADDITIONS PROCESS ✅ SUPPORTED

### 5.1 Adding New Books After Initial Upload ✅ COMPLETED
**Priority: HIGH** | **Complexity: LOW**

**Status**: Fully supported by existing commands since Phase 2 & 3.

#### Workflow Definition ✅
1. ✅ Export current database to CSV
2. ✅ Add new rows at end of CSV (or separate file)
3. ✅ Set `internal_id` for new books (must be unique)
4. ✅ Set all required fields
5. ✅ Upload any new PDF/image files
6. ✅ Import CSV with `mode: create_only` or `mode: upsert`

#### Artisan Commands ✅
- [x] Export: `php artisan books:export-csv --output=current-books.csv` ✅
- [x] Import new: `php artisan books:import-csv new-books.csv --mode=create_only` ✅
- [x] Update existing: `php artisan books:import-csv updated-books.csv --mode=update_only` ✅

**Example: Adding 50 New Books**:
```bash
# 1. Export current books for reference (optional)
php artisan books:export-csv --output=backup-$(date +%Y%m%d).csv

# 2. Prepare new-books.csv with 50 new books
# Use template: storage/csv-templates/book-import-template.csv

# 3. Preview what will be imported
php artisan books:import-csv new-books.csv --preview --mode=create_only

# 4. Import new books only (skip if already exists)
php artisan books:import-csv new-books.csv --mode=create_only --create-missing
```

### 5.2 Batch Update Process ✅ COMPLETED
**Priority: MEDIUM** | **Complexity: LOW**

**Status**: Fully supported by existing commands since Phase 2, 3, & 4.

#### Use Cases ✅
All supported:
- ✅ Update access levels for multiple books
- ✅ Add new classification values to existing books
- ✅ Bulk update publisher information
- ✅ Add keywords to multiple books
- ✅ Update any book fields in bulk

#### Workflow ✅
- [x] Export filtered subset of books ✅
- [x] Edit specific columns in CSV ✅
- [x] Re-import with `mode: update_only` ✅
- [x] Preview changes before applying ✅
- [x] Apply updates (import session provides audit trail) ✅

**Example: Bulk Update Access Levels**:
```bash
# 1. Export books from specific collection
php artisan books:export-csv --collection=1 --output=collection1-books.csv

# 2. Edit CSV: Change access_level column from 'limited' to 'full'

# 3. Preview changes
php artisan books:import-csv collection1-books.csv --preview --mode=update_only

# 4. Apply updates
php artisan books:import-csv collection1-books.csv --mode=update_only
```

**Example: Add Keywords to Multiple Books**:
```bash
# 1. Export books by language
php artisan books:export-csv --language=2 --output=chuukese-books.csv

# 2. Edit CSV: Add keywords to 'Keywords' column (pipe-separated)

# 3. Preview changes
php artisan books:import-csv chuukese-books.csv --preview --mode=upsert

# 4. Import updates
php artisan books:import-csv chuukese-books.csv --mode=upsert
```

**Note**: Section 5 workflows are fully operational. All commands and features were implemented in previous phases.

---

## 6. FILAMENT ADMIN INTERFACE ✅ COMPLETED

### 6.1 CSV Import Page ✅ COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Create Filament page: `CsvImport` (`/admin/csv-import`) ✅
- [x] File upload field (accepts .csv, .txt) ✅
- [x] Import mode selection (radio buttons) ✅
- [x] Configuration options (checkboxes): ✅
  - [x] Create missing relations ✅
  - [x] Skip invalid rows ✅
  - [ ] Validate file references *(Not implemented - validated during import)*
  - [ ] Send completion email *(Future enhancement)*
- [x] "Validate Only" button (dry run) ✅
- [x] "Import" button ✅
- [ ] Progress indicator (progress bar, percentage) *(Future enhancement - use queue worker)*
- [ ] Real-time log display (scrollable console output) *(Future enhancement)*
- [ ] Download error report button (if errors) *(Errors shown in import history)*
- [x] Link to import history ✅

**Deliverables**:
- ✅ `/app/Filament/Pages/CsvImport.php` - Custom import page
- ✅ `/resources/views/filament/pages/csv-import.blade.php` - Import page view
- ✅ File upload with validation
- ✅ Mode selection (upsert, create_only, update_only, create_duplicates)
- ✅ Validate and Import actions
- ✅ Template download links
- ✅ Quick help section

**Features Implemented**:
- File upload field with CSV/TXT acceptance
- Import mode radio buttons with descriptions
- Create missing relations checkbox
- Skip invalid rows checkbox
- Validate Only button (runs validation without importing)
- Import CSV button (with confirmation modal)
- Template download links (blank and example)
- Comprehensive help section
- Success/error notifications
- Link to view import details after completion

### 6.2 CSV Export Page ✅ COMPLETED
**Priority: HIGH** | **Complexity: LOW**

- [x] Create Filament page: `CsvExport` (`/admin/csv-export`) ✅
- [x] Filter options: ✅
  - [x] Date range picker (created dates) ✅
  - [x] Access level select ✅
  - [x] Collection select ✅
  - [x] Language select ✅
  - [x] Active/Featured toggles ✅
  - [x] Publication year range ✅
- [ ] Field selection (checkboxes for column groups) *(All fields always exported)*
- [x] Export format selection (CSV, TSV) ✅
- [ ] Excel, JSON formats *(Future enhancement)*
- [x] "Generate Export" button ✅
- [x] Download link (valid for 24 hours) ✅
- [ ] Export history table *(Future enhancement)*

**Deliverables**:
- ✅ `/app/Filament/Pages/CsvExport.php` - Custom export page
- ✅ `/resources/views/filament/pages/csv-export.blade.php` - Export page view
- ✅ Comprehensive filter system
- ✅ Format selection (CSV/TSV)
- ✅ Download functionality
- ✅ Quick help section

**Features Implemented**:
- Format selection (CSV/TSV) with descriptions
- Include BOM checkbox (CSV only)
- Include database mapping row checkbox
- Collection filter (searchable dropdown)
- Language filter (searchable dropdown)
- Access level filter
- Status filter (active/inactive)
- Created date range filter
- Publication year range filter
- Featured books filter
- Chunk size configuration
- Export button with immediate execution
- Download notification with file size
- 24-hour file expiration
- Comprehensive help section with use cases

### 6.3 Import History Resource ✅ COMPLETED
**Priority: MEDIUM** | **Complexity: LOW**

- [x] `csv_imports` database table already exists (from Phase 2) ✅
- [x] Create Filament resource: `CsvImportResource` ✅
- [x] List imports with filters (status, mode, errors, date) ✅
- [x] View import details page: ✅
  - [x] Summary stats ✅
  - [x] Error log (first 20 errors) ✅
  - [x] Success rate indicator ✅
  - [x] Timing information ✅
  - [ ] List of affected books (links) *(Future enhancement)*
  - [ ] Download original CSV *(Future enhancement)*
  - [ ] Rollback button *(Deferred - Section 4.4)*

**Deliverables**:
- ✅ `/app/Filament/Resources/CsvImportResource.php` - Import history resource
- ✅ `/app/Filament/Resources/CsvImportResource/Pages/ListCsvImports.php` - List page
- ✅ `/app/Filament/Resources/CsvImportResource/Pages/ViewCsvImport.php` - Detail view page

**Features Implemented**:
- Comprehensive import history table with columns:
  - Filename (searchable, sortable)
  - Imported by (user name)
  - Mode (badge with colors)
  - Status (badge with colors)
  - Total/Success/Failed counts
  - Created/Updated counts
  - Started time (human-readable "ago" format)
  - Duration
- Filters:
  - Status (pending, processing, completed, failed, cancelled)
  - Mode (create_only, update_only, upsert, create_duplicates)
  - Has errors (shows only imports with failures)
  - Recent (last 7 days)
- Detailed view page:
  - Import summary section
  - Statistics with color-coded metrics
  - Timing information
  - Success rate with color coding
  - Error log (first 20 errors with row/column info)
- Delete action for cleanup
- Links to import and export pages in header

### 6.4 CSV Template Download ✅ COMPLETED
**Priority: LOW** | **Complexity: LOW**

- [x] Add "Download CSV Template" link in: ✅
  - [x] CSV Import page ✅
  - [ ] Books list page (export action) *(Not implemented - accessible via import page)*
  - [ ] Documentation page *(Future enhancement)*
- [x] Download route for templates ✅
- [x] Blank template available ✅
- [x] Example template available ✅

**Deliverables**:
- ✅ Routes in `/routes/web.php` for template downloads
- ✅ Routes for export file downloads
- ✅ 24-hour expiration for export files
- ✅ Template links in import page

**Features Implemented**:
- Download blank template route (`/csv/download-template/blank`)
- Download example template route (`/csv/download-template/example`)
- Download export file route (`/csv/download-export/{filename}`)
- 24-hour automatic expiration for export files
- Template links in CSV Import page
- Field documentation link in import page

---

## 7. VALIDATION & DATA QUALITY

### 7.1 Pre-Import Validation ✅ MOSTLY COMPLETED
**Priority: HIGH** | **Complexity: MEDIUM**

- [x] Structure validation: ✅
  - [x] Required columns present ✅ (validateHeaders in BookCsvImportValidation.php:16-22)
  - [x] No unexpected columns (warning only) ✅ (validateHeaders in BookCsvImportValidation.php:24-29)
  - [x] Column count validation per row ✅ (validateCsv in BookCsvImportService.php:113-115)
  - [ ] Encoding is UTF-8 *(Deferred - handled by PHP's fgetcsv())*
  - [ ] No BOM issues *(Deferred - templates include BOM, no validation needed)*
- [x] Data type validation (per field) ✅
  - [x] String length validation ✅ (validateStringLengths in BookCsvImportValidation.php:57-66)
  - [x] Integer validation and ranges ✅ (validateIntegers in BookCsvImportValidation.php:71-100)
  - [x] Enum validation (access_level, physical_type) ✅ (validateEnums in BookCsvImportValidation.php:105-124)
  - [x] Required fields check ✅ (validateRow in BookCsvImportValidation.php:39-42)
- [x] Referential integrity validation: ✅
  - [x] Collections exist or can be created ✅ (resolveCollection in BookCsvImportRelationships.php:23-36)
  - [x] Publishers exist or can be created ✅ (resolvePublisher in BookCsvImportRelationships.php:42-55)
  - [x] Languages exist ✅ (resolveLanguage in BookCsvImportRelationships.php:96-107)
  - [x] Classification values exist ✅ (attachClassifications in BookCsvImportRelationships.php:226-228)
  - [x] Creators exist or can be created ✅ (resolveCreator in BookCsvImportRelationships.php:128-144)
- [x] Duplicate detection: ✅
  - [x] Check internal_id duplicates ✅ (findExistingBook in BookCsvImportService.php:500-519)
  - [x] Check palm_code duplicates ✅ (findExistingBook in BookCsvImportService.php:511-515)
- [ ] File reference validation: *(Deferred - optional enhancement)*
  - [ ] PDFs exist at specified path *(Not implemented - attachFile creates DB record without checking)*
  - [ ] Thumbnails exist at specified path *(Not implemented - attachFile creates DB record without checking)*
  - [ ] Audio files exist *(Not implemented)*
- [ ] Relationship validation: *(Deferred - optional enhancement)*
  - [x] Related book IDs exist ✅ (attachBookRelationships checks if related book exists before creating)
  - [ ] No circular relationships *(Not implemented - would require graph traversal)*

**Deliverables**:
- ✅ `/app/Services/BookCsvImportValidation.php` - Validation trait with comprehensive checks
- ✅ `/app/Console/Commands/CheckImportPrerequisites.php` - Pre-import environment checker
- ✅ Validation integrated into BookCsvImportService::validateCsv()
- ✅ Error and warning collection system

**Implementation Notes**:
- Validation runs in two modes:
  1. **Pre-validation** (--validate-only flag): Validates first 100 rows for performance
  2. **Import validation**: Full validation during actual import
- Errors vs Warnings:
  - **Errors**: Block import, must be fixed
  - **Warnings**: Allow import with notification
- Missing language behavior: Silently skips (doesn't throw error)
- Missing file behavior: Creates DB record without checking file existence
- Comprehensive prerequisite checker command: `php artisan books:check-prerequisites`

### 7.2 Post-Import Verification *(DEFERRED - Future Enhancement)*
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Run automated checks after import:
  - [ ] All books have titles
  - [ ] All books have valid access_level
  - [ ] Relationship counts match expected
  - [ ] File associations created correctly
- [ ] Generate data quality report
- [ ] Flag suspicious records for manual review:
  - [ ] Missing descriptions
  - [ ] No languages assigned
  - [ ] No classifications assigned
  - [ ] Missing files

**Status**: Not implemented. Can be added as future enhancement if needed for quality assurance. Current import system tracks success/failure counts and logs errors, which provides basic quality metrics.

### 7.3 Data Cleansing Tools *(DEFERRED - Future Enhancement)*
**Priority: LOW** | **Complexity: MEDIUM**

- [ ] Artisan commands for data cleanup:
  - [ ] `csv:fix-encoding` - Fix character encoding issues
  - [ ] `csv:trim-whitespace` - Remove extra spaces
  - [ ] `csv:normalize-years` - Standardize year formats
  - [ ] `csv:deduplicate` - Find and merge duplicates

**Status**: Not implemented. These are nice-to-have utilities that can be added if data quality issues arise. Current validation and import process handles most common data quality concerns automatically.

---

**Section 7 Completion Summary:**
Section 7.1 (Pre-Import Validation) is **mostly complete** with comprehensive validation implemented during Phase 2. The validation system includes:
- ✅ Structure and header validation
- ✅ Data type validation (strings, integers, enums)
- ✅ Referential integrity checks for all relationships
- ✅ Duplicate detection by internal_id and palm_code
- ⚠️ File existence validation deferred (creates DB records without checking files)
- ⚠️ Circular relationship detection deferred

Sections 7.2 and 7.3 are marked as **future enhancements**. The current system provides adequate validation for production use. The import system tracks success/failure rates, logs detailed errors, and the `CheckImportPrerequisites` command validates the environment before bulk uploads.

Files implemented:
- `/app/Services/BookCsvImportValidation.php` - Validation trait
- `/app/Console/Commands/CheckImportPrerequisites.php` - Environment checker
- Validation integrated into `BookCsvImportService::validateCsv()`

---

## 8. PERFORMANCE OPTIMIZATION

### 8.1 Large File Handling
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Stream CSV parsing (don't load entire file in memory)
- [ ] Process in chunks (100 rows per batch)
- [ ] Use database transactions per chunk
- [ ] Implement memory-efficient relationship loading
- [ ] Use eager loading to prevent N+1 queries
- [ ] Disable model events during bulk import (re-enable after)

### 8.2 Database Optimization
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Disable foreign key checks during import (re-enable after)
- [ ] Create temporary indexes for import matching
- [ ] Use bulk insert for pivot tables
- [ ] Optimize queries with proper indexes
- [ ] Consider using raw SQL for bulk operations

### 8.3 Benchmarking & Monitoring
**Priority: LOW** | **Complexity: LOW**

- [ ] Track import performance metrics:
  - Rows per second
  - Memory usage
  - Peak memory
  - Total time
- [ ] Set performance targets:
  - 1000 books in < 5 minutes
  - Memory usage < 512MB
- [ ] Log slow imports for investigation

---

## 9. DOCUMENTATION & TRAINING

### 9.1 User Documentation ✅ PARTIALLY COMPLETED
**Priority: MEDIUM** | **Complexity: LOW**

- [x] Create CSV Import Guide (Markdown):
  - Field definitions and examples ✅ `/docs/CSV_FIELD_MAPPING.md`
  - Multi-value field formatting (pipe separator) ✅
  - File path conventions ✅
  - Common errors and solutions ✅ `/docs/CSV_QUICK_REFERENCE.md`
- [x] Template Usage Guide ✅ `/storage/csv-templates/README.md`
- [ ] Create CSV Export Guide *(To be completed during export development)*
- [ ] Create Update Process Guide *(To be completed during re-import development)*
- [ ] Video tutorial (screen recording) *(Post-launch)*

### 9.2 Developer Documentation
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] API documentation for services
- [ ] Database schema documentation
- [ ] Extension guide (adding new CSV columns)
- [ ] Troubleshooting guide

### 9.3 Administrator Training
**Priority: LOW** | **Complexity: LOW**

- [ ] Create admin checklist for:
  - Initial bulk upload
  - Adding new books
  - Updating existing books
  - Troubleshooting failed imports
- [ ] Test scenarios with sample data

---

## 10. TESTING & QUALITY ASSURANCE

### 10.1 Unit Tests
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Test CSV parsing with various formats
- [ ] Test field mapping accuracy
- [ ] Test validation rules
- [ ] Test relationship resolution
- [ ] Test duplicate detection
- [ ] Test error handling

### 10.2 Integration Tests
**Priority: HIGH** | **Complexity: HIGH**

- [ ] Test full import flow (end-to-end)
- [ ] Test export then re-import (round-trip)
- [ ] Test large file import (1000+ rows)
- [ ] Test concurrent imports
- [ ] Test rollback functionality

### 10.3 Sample Data Sets
**Priority: HIGH** | **Complexity: LOW**

- [ ] Create minimal test CSV (10 books, all required fields)
- [ ] Create comprehensive test CSV (50 books, all features):
  - Multiple languages
  - Multiple creators
  - All classification types
  - Related books
  - Various file types
- [ ] Create edge case test CSV:
  - Special characters in titles
  - Very long descriptions
  - Missing optional fields
  - Duplicate records
  - Invalid references
- [ ] Store test CSVs in `/tests/fixtures/csv/`

### 10.4 Manual Testing Checklist
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Import test CSV successfully
- [ ] Verify all relationships created
- [ ] Export and compare with original
- [ ] Update books via CSV
- [ ] Add new books incrementally
- [ ] Test error scenarios (invalid data)
- [ ] Test UI responsiveness
- [ ] Test progress tracking
- [ ] Test error report download

---

## 11. SECURITY & PERMISSIONS

### 11.1 Access Control
**Priority: HIGH** | **Complexity: LOW**

- [ ] Restrict CSV import to admins only
- [ ] Log all import/export actions
- [ ] Require re-authentication for sensitive operations
- [ ] Rate limiting on import operations

### 11.2 Data Validation & Sanitization
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Sanitize all input data (prevent XSS)
- [ ] Validate file uploads (size, type, content)
- [ ] Check for malicious CSV content
- [ ] Limit file size (max 50MB)
- [ ] Scan uploaded files for malware (if applicable)

### 11.3 Backup & Recovery
**Priority: HIGH** | **Complexity: LOW**

- [ ] Automatic database backup before import
- [ ] Keep backup for 30 days
- [ ] Document restoration process
- [ ] Test backup/restore procedure

---

## 12. MIGRATION & DEPLOYMENT

### 12.1 Migration Plan ✅ COMPLETED
**Priority: HIGH** | **Complexity: LOW**

- [x] Create migration for `csv_imports` table ✅
- [x] Add `updated_by` field to books table *(Already exists - added earlier)*
- [x] Create storage directories:
  - [x] `/storage/csv-imports/` ✅
  - [x] `/storage/csv-exports/` ✅
  - [x] `/storage/csv-templates/` ✅
  - [x] `/storage/logs/csv-imports/` ✅
- [ ] Add necessary permissions *(Deferred - will be set during deployment)*

### 12.2 Configuration ✅ COMPLETED
**Priority: MEDIUM** | **Complexity: LOW**

- [x] Create config file: `config/csv-import.php`: ✅
  - Chunk size (100 rows)
  - Max file size (50MB)
  - Timeout settings (600 seconds)
  - Storage paths (all directories)
  - Email settings (placeholders)
  - Default import mode (upsert)
  - Field mappings (65+ fields)
  - Validation rules
  - Access level mappings
  - Physical type mappings
  - Classification/relationship type mappings
- [ ] Add environment variables for sensitive settings *(Optional - defaults work fine)*

### 12.3 Deployment Checklist
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Run migrations
- [ ] Create storage directories
- [ ] Set proper permissions on storage
- [ ] Generate default CSV template
- [ ] Test import/export in staging
- [ ] Perform test import with production data clone
- [ ] Deploy to production
- [ ] Verify functionality

---

## 13. FUTURE ENHANCEMENTS

### Phase 2 (Post-Launch)
- [ ] **Scheduled Exports**: Automatic weekly/monthly exports
- [ ] **API Endpoints**: RESTful API for CSV import/export
- [ ] **Webhooks**: Notify external systems of import completion
- [ ] **Advanced Mapping**: Custom field mapping interface
- [ ] **Excel Import**: Direct .xlsx import (bypass CSV conversion)
- [ ] **Incremental Sync**: Only import changed records
- [ ] **Conflict Resolution UI**: Interactive conflict resolution
- [ ] **Import Templates**: Save common import configurations
- [ ] **Multi-language Support**: CSV templates in multiple languages
- [ ] **Collaboration**: Multiple admins editing CSV simultaneously

### Phase 3 (Future)
- [ ] **Google Sheets Integration**: Direct import from Google Sheets
- [ ] **Dropbox/OneDrive Sync**: Automatic import from cloud storage
- [ ] **Version Control**: Track CSV file versions
- [ ] **Diff Tool**: Visual comparison of CSV versions
- [ ] **AI-Assisted Mapping**: Auto-detect column mappings
- [ ] **Data Enrichment**: Auto-populate missing metadata

---

## IMPLEMENTATION TIMELINE

### Week 1-2: Foundation
- CSV field mapping definition
- Core import service structure
- Basic validation system
- CSV template generator

### Week 3-4: Import Core
- Relationship resolution
- Error handling & reporting
- Duplicate detection
- Progress tracking

### Week 5-6: Export & Re-import
- Export service
- Relationship flattening
- Change detection
- Update strategy

### Week 7-8: Admin Interface
- Filament import page
- Export page
- Import history resource
- Progress indicators

### Week 9-10: Testing & Polish
- Unit tests
- Integration tests
- Documentation
- Performance optimization

### Week 11: Initial Bulk Upload
- Prepare production data
- Run validation
- Execute import
- Verify data integrity

### Week 12: Training & Handoff
- Admin training
- Documentation finalization
- Support setup
- Monitoring

---

## SUCCESS CRITERIA

- [ ] Successfully import 1000+ books from CSV in < 10 minutes
- [ ] Zero data loss during import/export round-trip
- [ ] Comprehensive error reporting with actionable messages
- [ ] Admin can export, edit, and re-import with < 5 clicks
- [ ] All relationships preserved (languages, creators, classifications)
- [ ] File references correctly associated
- [ ] Audit trail tracks all changes
- [ ] Admin interface intuitive for non-technical users
- [ ] Documentation covers all common scenarios
- [ ] Test coverage > 80% for critical paths

---

## NOTES & CONSIDERATIONS

### CSV Format Standards
- UTF-8 encoding (with BOM for Excel)
- Comma separator (configurable to tab/semicolon)
- Double-quote text delimiter
- Escaped quotes: `""`
- Line endings: `\n` (Unix) or `\r\n` (Windows)

### Multi-value Separator
- Default: Pipe `|` (configurable)
- Alternative: Semicolon `;` (if pipe appears in data)
- Escape character: Backslash `\` (for literal separators)

### File Path Conventions
- Absolute paths: `/var/www/storage/app/public/books/pdfs/book1.pdf`
- Relative to storage: `books/pdfs/book1.pdf`
- Filename only: `book1.pdf` (assumes default directory)

### Performance Targets
- 1000 books: < 5 minutes
- 2000 books: < 10 minutes
- Memory usage: < 512MB
- CPU usage: < 80% during import

### Error Tolerance
- Critical errors: Stop import immediately
  - Invalid CSV structure
  - Database connection lost
- Non-critical errors: Log and continue
  - Missing optional references
  - File not found warnings
  - Invalid optional fields

---

## DEPENDENCIES

### Laravel Packages
- `league/csv` - CSV parsing and generation
- `maatwebsite/excel` - Excel import/export (optional)
- `spatie/laravel-medialibrary` - File management (if used)

### Filament Packages
- `filament/filament` - Admin panel
- `filament/forms` - Form fields
- `filament/tables` - Data tables
- `filament/notifications` - User notifications

### External Resources
- Storage space for CSV files (50MB+ per export)
- Queue worker for background processing
- Email service for notifications

---

**Document Version**: 1.0
**Last Updated**: 2025-11-07
**Maintained By**: Development Team
**Review Cycle**: After each milestone completion
