# Bulk Book Import Guide

## Overview
This guide provides step-by-step instructions for performing the initial bulk upload of 1000+ books into the library system using CSV import.

---

## Prerequisites Checklist

Before beginning the import process, ensure all requirements are met:

### 1. Database Prerequisites

Run the prerequisite checker to verify all required data exists:

```bash
php artisan books:check-prerequisites --detailed
```

#### Required Data

- **✅ Languages**: At least basic languages must exist (English, Chuukese, Pohnpeian, etc.)
  - Languages CANNOT be auto-created during import
  - Must be added via database seeder or admin panel first

- **⚠️ Collections**: Optional but recommended
  - Can be auto-created during import with `--create-missing` flag
  - Better to create manually for proper organization

- **⚠️ Publishers**: Optional but recommended
  - Can be auto-created during import with `--create-missing` flag
  - Includes publisher name and optional program name

- **✅ Classification Types**: Must have all 6 types
  - Purpose
  - Genre
  - Sub-genre
  - Type
  - Themes-Uses
  - Learner-Level

- **⚠️ Classification Values**: Optional but recommended
  - Values like "Science", "Mathematics", "Fiction", etc.
  - Can be referenced during import if they exist

- **⚠️ Geographic Locations**: Optional
  - Islands, states, and regions
  - Used for location tagging

- **⚠️ Creators**: Optional
  - Authors, illustrators, editors
  - Can be auto-created during import with `--create-missing` flag

### 2. File Storage Prerequisites

#### Directory Structure

All storage directories must exist and be writable:

```bash
# Create required directories
mkdir -p storage/csv-imports
mkdir -p storage/csv-exports
mkdir -p storage/csv-templates
mkdir -p storage/logs/csv-imports
mkdir -p storage/app/public/books/pdfs
mkdir -p storage/app/public/books/thumbnails
mkdir -p storage/app/public/books/audio
mkdir -p storage/app/public/books/video

# Set proper permissions
chmod -R 775 storage/
```

#### File Upload

Before importing:

1. **PDF Files**: Upload all book PDFs to `/storage/app/public/books/pdfs/`
   - File names must match exactly as specified in CSV
   - Recommended naming: `{palm_code}.pdf` or `{internal_id}.pdf`

2. **Thumbnail Images**: Upload all cover images to `/storage/app/public/books/thumbnails/`
   - Recommended naming: `{palm_code}_thumb.jpg`
   - Supported formats: JPG, PNG, GIF

3. **Audio Files** (optional): Upload to `/storage/app/public/books/audio/`

4. **Video Files** (optional): Upload to `/storage/app/public/books/video/`

### 3. CSV File Preparation

#### Use the Template

Start with the provided CSV template:

```bash
# Download blank template
cp storage/csv-templates/book-import-template.csv /path/to/your-books.csv

# Or view example
cat storage/csv-templates/book-import-example.csv
```

#### CSV Requirements

- **Encoding**: UTF-8 with BOM (for Excel compatibility)
- **Headers**: Two-row header system
  - Row 1: Human-readable column names
  - Row 2: Database field mappings
- **Separator**: Comma (`,`)
- **Multi-value separator**: Pipe (`|`)
- **Required fields**:
  - `ID` (internal_id)
  - `Title`
  - `Primary_language`

#### Field Reference

See comprehensive field documentation:
- `/docs/CSV_FIELD_MAPPING.md` - Complete reference for all 65+ fields
- `/docs/CSV_QUICK_REFERENCE.md` - Quick reference guide

---

## Import Process

### Step 1: Verify Prerequisites

```bash
# Check all prerequisites
php artisan books:check-prerequisites --detailed

# Check specific CSV file
php artisan books:check-prerequisites --csv-file=/path/to/books.csv
```

**Expected Output**: All checks should pass (green ✓) or have only warnings (yellow ⚠️). Fix any red issues (✗).

### Step 2: Validation-Only Run

**IMPORTANT**: Always run validation first before actual import!

```bash
php artisan books:import-csv /path/to/books.csv --validate-only
```

This will:
- ✅ Check CSV structure and encoding
- ✅ Validate all required fields
- ✅ Check data types and formats
- ✅ Validate enum values
- ✅ Check for duplicates
- ✅ Verify references (collections, publishers, languages)
- ✅ Generate detailed validation report

**Review the validation report carefully!**

### Step 3: Fix Validation Errors

If validation fails:

1. Review error messages with row numbers
2. Fix issues in CSV file
3. Re-run validation (Step 2)
4. Repeat until validation passes

Common validation errors:
- Missing required fields (title, internal_id)
- Invalid access_level values (must be Y/N/L)
- Invalid physical_type values
- Duplicate internal_ids or palm_codes
- Non-existent language references
- Invalid publication years

### Step 4: Perform Actual Import

Once validation passes, run the actual import:

#### Recommended Configuration for Initial Upload

```bash
php artisan books:import-csv /path/to/books.csv \
  --mode=upsert \
  --create-missing \
  --skip-invalid
```

**Flags Explained**:
- `--mode=upsert`: Create new books or update existing (recommended for initial upload)
- `--create-missing`: Auto-create missing collections, publishers, creators
- `--skip-invalid`: Skip rows with errors instead of stopping entire import

#### Alternative Modes

**Create Only** (fail if book already exists):
```bash
php artisan books:import-csv /path/to/books.csv --mode=create_only
```

**Update Only** (only update existing books):
```bash
php artisan books:import-csv /path/to/books.csv --mode=update_only
```

**Create Duplicates** (allow duplicate books with new IDs):
```bash
php artisan books:import-csv /path/to/books.csv --mode=create_duplicates
```

### Step 5: Monitor Progress

The import command will display real-time progress:

```
Starting CSV import...
Mode: upsert
Options:
  - Create missing relations: Yes
  - Skip invalid rows: Yes

Processing: ████████████████████░░░░░░░░  75%

Processed: 750 / 1000 rows
Created: 680
Updated: 45
Failed: 25
```

For background imports (large files):
```bash
# Dispatch to queue
php artisan books:import-csv /path/to/books.csv --async

# Check progress
php artisan queue:work --once
```

### Step 6: Review Import Summary

After completion, review the summary report:

```
Import completed successfully!

Summary:
  Total rows: 1000
  Processed: 975
  Successful: 950
  Failed: 25
  Created: 875
  Updated: 75

Duration: 3 minutes 42 seconds
```

If there are failed rows, check the error log for details.

### Step 7: Verify Data Integrity

After import, perform manual spot checks:

1. **Check book counts**:
```bash
# In MySQL or via Tinker
php artisan tinker
>>> App\Models\Book::count()
```

2. **Verify relationships**:
```bash
# Check language associations
>>> App\Models\Book::with('languages')->find(1)

# Check creators
>>> App\Models\Book::with('creators')->find(1)

# Check classifications
>>> App\Models\Book::with('classifications')->find(1)
```

3. **Browse admin panel**: `/admin/books`
   - Spot check random books
   - Verify metadata is correct
   - Check file associations
   - Verify relationships

4. **Test library frontend**:
   - Search for books
   - Filter by categories
   - View book detail pages
   - Test PDF viewing/downloading

---

## Import Configuration Reference

### Mode Options

| Mode | Description | Use Case |
|------|-------------|----------|
| `create_only` | Only create new books, skip existing | Adding new books to existing library |
| `update_only` | Only update existing books, skip new | Bulk update of existing book metadata |
| `upsert` | Create new or update existing | **Recommended for initial upload** |
| `create_duplicates` | Allow duplicate books with new IDs | Special cases only |

### Additional Options

| Flag | Description | Default |
|------|-------------|---------|
| `--create-missing` | Auto-create missing collections, publishers, creators | false |
| `--skip-invalid` | Skip rows with errors instead of failing entire import | false |
| `--validate-only` | Only validate, don't import | false |
| `--async` | Run import in background queue | false |

---

## Troubleshooting

### Import Fails Immediately

**Error**: "CSV structure validation failed"
- **Cause**: CSV headers don't match expected format
- **Fix**: Use the provided template, ensure two-row headers

**Error**: "No languages found"
- **Cause**: Languages table is empty
- **Fix**: Create languages via seeder or admin panel first

### Partial Import Failure

**Error**: "Row 453: Language 'Yapese' not found"
- **Cause**: Referenced language doesn't exist in database
- **Fix**: Add missing language or correct CSV

**Error**: "Row 782: Duplicate internal_id 'PALM001'"
- **Cause**: Duplicate ID in CSV or database
- **Fix**: Ensure all internal_ids are unique

### File Association Issues

**Error**: "Row 234: PDF file not found: book234.pdf"
- **Cause**: PDF file not uploaded or wrong filename
- **Fix**: Upload missing PDF or correct filename in CSV

### Memory Issues

**Error**: "Allowed memory size exhausted"
- **Cause**: Large CSV file, insufficient PHP memory
- **Fix**:
  1. Increase PHP memory limit in `php.ini`
  2. Use smaller batch size in config
  3. Split CSV into smaller files

### Timeout Issues

**Error**: "Maximum execution time exceeded"
- **Cause**: Import taking too long
- **Fix**:
  1. Increase timeout in `config/csv-import.php`
  2. Use `--async` flag for background processing
  3. Split CSV into smaller batches

---

## Performance Expectations

### Typical Import Speed

- **Small imports** (< 100 books): 10-30 seconds
- **Medium imports** (100-500 books): 1-3 minutes
- **Large imports** (500-1000 books): 3-8 minutes
- **Very large imports** (1000+ books): 8-15 minutes

### Factors Affecting Speed

- Number of relationships per book
- Database server performance
- File validation enabled/disabled
- Number of auto-created relations
- Queue driver (sync vs database vs redis)

---

## Post-Import Tasks

### 1. Verify Counts

```sql
-- Total books
SELECT COUNT(*) FROM books;

-- Books by access level
SELECT access_level, COUNT(*) FROM books GROUP BY access_level;

-- Books by collection
SELECT c.name, COUNT(b.id)
FROM collections c
LEFT JOIN books b ON b.collection_id = c.id
GROUP BY c.id;
```

### 2. Check for Missing Data

```sql
-- Books without languages
SELECT id, title FROM books
WHERE id NOT IN (SELECT DISTINCT book_id FROM book_languages);

-- Books without classifications
SELECT id, title FROM books
WHERE id NOT IN (SELECT DISTINCT book_id FROM book_classifications);

-- Books without files
SELECT id, title FROM books
WHERE id NOT IN (SELECT DISTINCT book_id FROM book_files);
```

### 3. Update Search Index

If using full-text search:
```bash
php artisan scout:import "App\Models\Book"
```

### 4. Clear Caches

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## Re-Import / Update Process

### Exporting Current Data

Before making bulk changes, export current state:

```bash
# Export all books
php artisan books:export-csv --output=backup-$(date +%Y%m%d).csv

# Export specific collection
php artisan books:export-csv --collection=1 --output=collection1.csv

# Export by date range
php artisan books:export-csv --created-from=2024-01-01 --created-to=2024-12-31
```

### Editing and Re-importing

1. Export current data
2. Edit CSV file (update metadata, add new books, etc.)
3. Validate changes: `php artisan books:import-csv file.csv --validate-only`
4. Import with appropriate mode:
   - New books only: `--mode=create_only`
   - Updates only: `--mode=update_only`
   - Both: `--mode=upsert`

### Incremental Updates

For adding new books to existing library:

```bash
# Export current state (optional backup)
php artisan books:export-csv --output=backup.csv

# Import new books only
php artisan books:import-csv new-books.csv --mode=create_only --create-missing
```

---

## Best Practices

### 1. Always Backup First

```bash
# Database backup
php artisan db:backup

# Export current books
php artisan books:export-csv --output=pre-import-backup-$(date +%Y%m%d).csv
```

### 2. Test with Sample Data

Before importing 1000+ books:
1. Create test CSV with 10-20 books
2. Run validation
3. Import test data
4. Verify results
5. Delete test data
6. Proceed with full import

### 3. Use Version Control for CSV

Keep track of CSV file versions:
```bash
git add books-import-v1.csv
git commit -m "Initial book import CSV - 1000 books"
```

### 4. Document Custom Mappings

If you customize field mappings in `config/csv-import.php`, document the changes.

### 5. Schedule Regular Exports

Set up automated backups:
```bash
# Add to crontab for weekly exports
0 2 * * 0 cd /path/to/app && php artisan books:export-csv --output=weekly-backup.csv
```

---

## Support & Resources

### Documentation
- `/docs/CSV_FIELD_MAPPING.md` - Complete field reference
- `/docs/CSV_QUICK_REFERENCE.md` - Quick reference guide
- `/storage/csv-templates/README.md` - Template usage guide
- `/CSV_IMPORT_TODO.md` - Implementation TODO and status

### Commands Reference
```bash
# Prerequisites check
php artisan books:check-prerequisites [--detailed] [--csv-file=FILE]

# Import
php artisan books:import-csv FILE [--mode=MODE] [--create-missing] [--skip-invalid] [--validate-only]

# Export
php artisan books:export-csv [--output=FILE] [--collection=ID] [--language=ID] [filters...]

# Help
php artisan books:import-csv --help
php artisan books:export-csv --help
```

### Getting Help

If you encounter issues not covered in this guide:
1. Check validation error messages carefully
2. Review the error log in `storage/logs/csv-imports/`
3. Run prerequisites checker: `php artisan books:check-prerequisites`
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Last Updated**: 2025-11-07
**Version**: 1.0
**Maintained By**: Development Team
