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
9. `/storage/csv-imports/`, `/storage/csv-exports/`, `/storage/logs/csv-imports/` - Directories

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
- ✅ **Section 3.4**: Export Formats - CSV format complete

**Deliverables**:
1. `/app/Services/BookCsvExportService.php` - Complete export service (~600 lines)
2. `/app/Console/Commands/ExportBooksToCsv.php` - CLI export command

**Key Features Implemented**:
- ✅ Export all books or filtered subsets
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
- ✅ UTF-8 with BOM for Excel compatibility
- ✅ Two-row headers (readable + database mapping)
- ✅ Chunked processing for memory efficiency
- ✅ CLI command with extensive filter options

**Next Steps**: Proceed to Section 4 (Re-import of Edited CSV) when needed

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

### 2.7 Initial Bulk Upload Process
**Priority: HIGH** | **Complexity: HIGH**

#### Pre-Import Checklist
- [ ] Ensure all related data exists:
  - Collections
  - Publishers
  - Languages
  - Classification Types & Values
  - Creators (or enable auto-creation)
  - Geographic Locations
- [ ] Upload all PDF files to `/storage/app/public/books/pdfs/`
- [ ] Upload all thumbnail images to `/storage/app/public/books/thumbnails/`
- [ ] Prepare CSV file with 1000+ book records

#### Import Configuration for Initial Upload
```php
[
    'mode' => 'upsert',
    'create_missing_relations' => true,
    'validate_file_references' => true,
    'skip_invalid_rows' => false,  // Fail if any critical errors
    'send_completion_email' => true,
]
```

#### Execution Steps
- [ ] Run validation-only pass first: `csv:import --validate-only`
- [ ] Review validation report
- [ ] Fix any errors in CSV
- [ ] Run actual import: `csv:import --file=books.csv`
- [ ] Monitor progress in admin panel
- [ ] Review import summary
- [ ] Verify data integrity with spot checks

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
- [ ] TSV: Tab-separated (better for texts with commas) *(Future enhancement)*
- [ ] Excel: Generate .xlsx file with formatting *(Future enhancement)*
- [ ] JSON: For programmatic use *(Future enhancement)*

**Current Implementation**:
- CSV format fully implemented with proper escaping
- UTF-8 encoding with optional BOM for Excel compatibility
- Configurable separator (default: comma)
- Two-row header system (readable + database mapping)

**Command Usage**:
```bash
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
```

---

## 4. RE-IMPORT OF EDITED CSV

### 4.1 Change Detection System
**Priority: HIGH** | **Complexity: HIGH**

- [ ] Compare imported CSV with existing database records
- [ ] Detect changes in:
  - Core book fields
  - Added/removed relationships
  - Modified relationships
- [ ] Generate change preview report
- [ ] Show before/after comparison for each changed field
- [ ] Require admin approval before applying changes

### 4.2 Update Strategy
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Match records by `internal_id` (primary)
- [ ] If internal_id missing, match by `palm_code`
- [ ] If both missing, match by exact title
- [ ] Handle missing records:
  - Skip if not found (with warning)
  - Create as new record if option enabled
- [ ] Handle relationship updates:
  - Replace vs. merge strategies
  - Remove existing and add new (replace)
  - Keep existing and add new (merge)

### 4.3 Audit Trail
**Priority: MEDIUM** | **Complexity: MEDIUM**

- [ ] Create `book_updates` table to track all changes
- [ ] Record:
  - Book ID
  - Updated by (user_id)
  - Update source (csv_import)
  - Changed fields (JSON)
  - Old values (JSON)
  - New values (JSON)
  - Timestamp
- [ ] Add `updated_by` foreign key to books table
- [ ] Display update history in admin panel

### 4.4 Rollback Capability
**Priority: LOW** | **Complexity: HIGH**

- [ ] Store snapshot of database state before import
- [ ] Allow rollback to pre-import state
- [ ] Implement undo last import functionality
- [ ] Time limit on rollback (24 hours?)

---

## 5. INCREMENTAL ADDITIONS PROCESS

### 5.1 Adding New Books After Initial Upload
**Priority: HIGH** | **Complexity: LOW**

#### Workflow Definition
1. Export current database to CSV
2. Add new rows at end of CSV (or separate file)
3. Set `internal_id` for new books (must be unique)
4. Set all required fields
5. Upload any new PDF/image files
6. Import CSV with `mode: create_only` or `mode: upsert`

#### Artisan Commands
- [ ] `csv:export --output=current-books.csv`
- [ ] `csv:import --file=new-books.csv --mode=create_only`
- [ ] `csv:import --file=updated-books.csv --mode=update_only`

### 5.2 Batch Update Process
**Priority: MEDIUM** | **Complexity: LOW**

#### Use Cases
- Update access levels for multiple books
- Add new classification values to existing books
- Bulk update publisher information
- Add keywords to multiple books

#### Workflow
- [ ] Export filtered subset of books
- [ ] Edit specific columns in CSV
- [ ] Re-import with `mode: update_only`
- [ ] Preview changes before applying
- [ ] Apply updates with audit trail

---

## 6. FILAMENT ADMIN INTERFACE

### 6.1 CSV Import Page
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Create Filament page: `CsvImport` (`/admin/csv-import`)
- [ ] File upload field (accepts .csv, .txt)
- [ ] Import mode selection (radio buttons)
- [ ] Configuration options (checkboxes):
  - Create missing relations
  - Validate file references
  - Skip invalid rows
  - Send completion email
- [ ] "Validate Only" button (dry run)
- [ ] "Import" button
- [ ] Progress indicator (progress bar, percentage)
- [ ] Real-time log display (scrollable console output)
- [ ] Download error report button (if errors)
- [ ] Import history table (past imports with status)

### 6.2 CSV Export Page
**Priority: HIGH** | **Complexity: LOW**

- [ ] Create Filament page: `CsvExport` (`/admin/csv-export`)
- [ ] Filter options:
  - Date range picker (created/updated)
  - Access level multi-select
  - Collection multi-select
  - Language multi-select
  - Active/Featured toggles
- [ ] Field selection (checkboxes for column groups):
  - Core fields
  - Relationships
  - Files
  - Analytics
  - System fields
- [ ] Export format selection (CSV, TSV, Excel, JSON)
- [ ] "Generate Export" button
- [ ] Download link (valid for 24 hours)
- [ ] Export history table

### 6.3 Import History Resource
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Create `csv_imports` database table:
  - id, user_id, filename, status, mode, rows_processed,
    rows_succeeded, rows_failed, error_log, started_at,
    completed_at, created_at
- [ ] Create Filament resource: `CsvImportResource`
- [ ] List imports with filters (status, user, date)
- [ ] View import details page:
  - Summary stats
  - Error log
  - List of affected books (links)
  - Download original CSV
  - Rollback button (if eligible)

### 6.4 CSV Template Download
**Priority: LOW** | **Complexity: LOW**

- [ ] Add "Download CSV Template" link in:
  - CSV Import page
  - Books list page (export action)
  - Documentation page
- [ ] Generate fresh template on-demand
- [ ] Include sample data row

---

## 7. VALIDATION & DATA QUALITY

### 7.1 Pre-Import Validation
**Priority: HIGH** | **Complexity: MEDIUM**

- [ ] Structure validation:
  - Required columns present
  - No unexpected columns (warning only)
  - Encoding is UTF-8
  - No BOM issues
- [ ] Data type validation (per field)
- [ ] Referential integrity validation:
  - Collections exist or can be created
  - Publishers exist or can be created
  - Languages exist
  - Classification values exist
  - Creators exist or can be created
- [ ] File reference validation:
  - PDFs exist at specified path
  - Thumbnails exist at specified path
- [ ] Relationship validation:
  - Related book IDs exist
  - No circular relationships

### 7.2 Post-Import Verification
**Priority: MEDIUM** | **Complexity: LOW**

- [ ] Run automated checks after import:
  - All books have titles
  - All books have valid access_level
  - Relationship counts match expected
  - File associations created correctly
- [ ] Generate data quality report
- [ ] Flag suspicious records for manual review:
  - Missing descriptions
  - No languages assigned
  - No classifications assigned
  - Missing files

### 7.3 Data Cleansing Tools
**Priority: LOW** | **Complexity: MEDIUM**

- [ ] Artisan commands for data cleanup:
  - `csv:fix-encoding` - Fix character encoding issues
  - `csv:trim-whitespace` - Remove extra spaces
  - `csv:normalize-years` - Standardize year formats
  - `csv:deduplicate` - Find and merge duplicates

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
**Last Updated**: 2025-11-06
**Maintained By**: Development Team
**Review Cycle**: After each milestone completion
