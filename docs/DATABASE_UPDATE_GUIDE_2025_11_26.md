# Database Update Guide - November 26, 2025

## Overview
This guide provides step-by-step instructions to update the database structure for the new CSV format with:
- Separate Description and Abstract fields
- New relationship type: "Translated"
- Expanded library link system (5 libraries)
- New book identifiers table (OCLC, ISBN, Other)

---

## 📋 Prerequisites

- Docker environment running
- Database accessible
- Backup of current database (if you have production data)
- Terminal access to project

---

## 🚀 Step-by-Step Implementation

### STEP 1: Backup Current Database (Optional but Recommended)

```bash
# Export current database structure and data
docker-compose exec app php artisan db:dump

# Or manually via mysqldump
docker-compose exec database mysqldump -u root -proot book_library > backup_$(date +%Y%m%d).sql
```

---

### STEP 2: Clear All Book Data

Since you mentioned you only have test data, let's clear it all:

```bash
# Use the new command to reset all book data
docker-compose exec app php artisan books:reset --force
```

**Expected Output:**
```
🗑️  Deleting all book-related data...
   ✓ Truncated book_views (X records)
   ✓ Truncated book_downloads (X records)
   ...
   ✓ Truncated books (X records)

✅ All book data has been deleted successfully!
```

---

### STEP 3: Run New Migrations

```bash
# Run all new migrations
docker-compose exec app php artisan migrate

# You should see these 4 new migrations execute:
# - 2025_11_26_203600_add_notes_version_and_abstract_to_books_table
# - 2025_11_26_203601_add_translated_relationship_type
# - 2025_11_26_203602_add_library_links_to_library_references
# - 2025_11_26_203603_create_book_identifiers_table
```

**Expected Output:**
```
  2025_11_26_203600_add_notes_version_and_abstract_to_books_table ........ 50ms DONE
  2025_11_26_203601_add_translated_relationship_type ..................... 25ms DONE
  2025_11_26_203602_add_library_links_to_library_references .............. 30ms DONE
  2025_11_26_203603_create_book_identifiers_table ........................ 40ms DONE
```

---

### STEP 4: Verify Database Structure

```bash
# Check that all new fields exist
docker-compose exec database mysql -u root -proot book_library -e "DESCRIBE books;"
docker-compose exec database mysql -u root -proot book_library -e "DESCRIBE library_references;"
docker-compose exec database mysql -u root -proot book_library -e "DESCRIBE book_identifiers;"
docker-compose exec database mysql -u root -proot book_library -e "SHOW COLUMNS FROM book_relationships LIKE 'relationship_type';"
```

**Expected Results:**

**books table** should include:
- `notes_version` (text, nullable)
- `abstract` (text, nullable)
- `description` (text, nullable) - comment updated

**library_references table** should include:
- `main_link` (varchar 500, nullable)
- `alt_link` (varchar 500, nullable)

**book_identifiers table** should exist with:
- `id`, `book_id`, `identifier_type`, `identifier_value`, `notes`, `created_at`, `updated_at`

**book_relationships.relationship_type** should include `'translated'` in enum values

---

### STEP 5: Re-seed Base Data

```bash
# Re-seed all the foundational data (languages, classifications, etc.)
docker-compose exec app php artisan db:seed --class=DatabaseSeeder
```

This will recreate:
- Languages
- Classification Types & Values
- Geographic Locations
- Initial Publishers
- Initial Collections
- **NEW:** Additional library codes (MARC, MICSEM, LIB5)

---

### STEP 6: Verify Models Are Updated

All models have been updated. Verify by checking:

```bash
# Check Book model has new fillable fields
docker-compose exec app php artisan tinker
>>> \App\Models\Book::first()->getFillable();
>>> // Should include 'notes_version' and 'abstract'
>>> exit

# Check BookIdentifier model exists
docker-compose exec app php artisan tinker
>>> \App\Models\BookIdentifier::getTypes();
>>> // Should return array of identifier types
>>> exit

# Check BookRelationship has new type
docker-compose exec app php artisan tinker
>>> \App\Models\BookRelationship::getTypes();
>>> // Should include 'translated' => 'Translated'
>>> exit
```

---

### STEP 7: Clear All Caches

```bash
# Clear all Laravel caches
docker-compose exec app php artisan optimize:clear

# Or individually:
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

---

### STEP 8: Update CSV Import Service (Manual Step)

⚠️ **IMPORTANT:** You need to manually update the CSV import service files:

**File:** `/app/Services/BookCsvImportService.php`

1. Update `createOrUpdateBook()` method to include new fields:
   ```php
   'notes_version' => $rowData['Notes related to version.'] ?? null,
   'abstract' => $rowData['ABSTRACT'] ?? null,
   'description' => $rowData['DESCRIPTION'] ?? null,
   ```

**File:** `/app/Services/BookCsvImportRelationships.php`

2. Update relationship type mapping for new "Related (translated)" column
3. Update `createLibraryReferences()` to handle 10 new library link columns
4. Add new `createBookIdentifiers()` method for BR, BS, BT columns

**📄 See full details in:** `/docs/CSV_IMPORT_SERVICE_UPDATES.md`

---

### STEP 9: Test CSV Import

```bash
# Place your NEW-BATCH.zip contents in appropriate folder
# Import via admin panel or artisan command

# Example if you have a CSV import command:
docker-compose exec app php artisan books:import /path/to/main-master-table.csv
```

---

### STEP 10: Verify Import Results

After importing your 41-book test batch:

```bash
# Check books were imported
docker-compose exec app php artisan tinker
>>> \App\Models\Book::count();
>>> // Should be 41

# Check new fields are populated
>>> $book = \App\Models\Book::first();
>>> $book->notes_version;
>>> $book->abstract;
>>> $book->description;

# Check book identifiers
>>> $book->bookIdentifiers()->count();
>>> $book->bookIdentifiers;

# Check library references with new fields
>>> $book->libraryReferences()->where('library_code', 'MARC')->first();
>>> $book->libraryReferences()->where('library_code', 'MICSEM')->first();

# Check translated relationships
>>> \App\Models\BookRelationship::where('relationship_type', 'translated')->count();

>>> exit
```

---

## 📝 Summary of Changes

### Database Tables

✅ **books** - Added 2 fields:
- `notes_version` (text)
- `abstract` (text)

✅ **book_relationships** - Updated enum:
- Added `'translated'` to relationship_type

✅ **library_references** - Added 2 fields:
- `main_link` (varchar 500)
- `alt_link` (varchar 500)

✅ **book_identifiers** - NEW TABLE:
- Complete new table for managing book identifiers

### Models Updated

✅ **Book.php** - Added:
- `notes_version` and `abstract` to fillable
- `bookIdentifiers()` relationship
- `translatedBooks()` relationship

✅ **BookIdentifier.php** - NEW MODEL:
- Complete model with types and relationships

✅ **LibraryReference.php** - Added:
- `main_link` and `alt_link` to fillable
- New library code constants (MARC, MICSEM, LIB5)
- New scopes and helper methods

✅ **BookRelationship.php** - Added:
- `TYPE_TRANSLATED` constant
- `translated()` scope

### Commands Added

✅ **books:reset** - New command to clear all book data

---

## 🔧 Troubleshooting

### Migration Errors

**Error:** "Column already exists"
```bash
# Rollback last migration batch and re-run
docker-compose exec app php artisan migrate:rollback
docker-compose exec app php artisan migrate
```

**Error:** "SQLSTATE[42S01]: Base table or view already exists"
```bash
# Check if migration already ran
docker-compose exec database mysql -u root -proot book_library -e "SELECT * FROM migrations ORDER BY id DESC LIMIT 5;"
```

### Import Errors

**Error:** "Unknown column 'notes_version'"
```bash
# Verify migration ran successfully
docker-compose exec database mysql -u root -proot book_library -e "SHOW COLUMNS FROM books LIKE 'notes_version';"
```

**Error:** "Class 'BookIdentifier' not found"
```bash
# Clear autoload cache
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize:clear
```

---

## 📚 Related Documentation

- `/docs/CSV_IMPORT_SERVICE_UPDATES.md` - Detailed CSV import changes
- `/docs/CSV_FIELD_MAPPING.md` - Complete field mapping (needs update)
- `/docs/CSV_TO_DATABASE_MAPPING.md` - Database mapping (needs update)
- `/database/migrations/2025_11_26_*` - New migration files

---

## ✅ Verification Checklist

After completing all steps:

- [ ] All 4 new migrations executed successfully
- [ ] All book data cleared from database
- [ ] Base data re-seeded (languages, classifications, etc.)
- [ ] Book model has `notes_version` and `abstract` in fillable
- [ ] BookIdentifier model exists and loads
- [ ] LibraryReference model has new fields
- [ ] BookRelationship has 'translated' type
- [ ] CSV import service updated (see CSV_IMPORT_SERVICE_UPDATES.md)
- [ ] Test CSV import completed successfully with 41 books
- [ ] New fields populated correctly from CSV
- [ ] Book identifiers created for OCLC, ISBN, Other
- [ ] Library references created for MARC, MICSEM, LIB5
- [ ] Translated relationships working

---

## 🎯 Next Steps After Database Update

1. **Update Filament Resources** (if needed)
   - Add new fields to BookResource forms
   - Add BookIdentifier as relation manager
   - Update LibraryReference resource

2. **Update Front-end Views** (if needed)
   - Display separate description and abstract
   - Show book identifiers on book detail pages
   - Display library links

3. **Update Documentation**
   - CSV_FIELD_MAPPING.md
   - CSV_TO_DATABASE_MAPPING.md
   - API documentation (if exists)

4. **Test Thoroughly**
   - Import all 41 books from NEW-BATCH
   - Verify all relationships
   - Test search/filter functionality
   - Verify admin panel displays correctly

---

**Document Created:** 2025-11-26
**Last Updated:** 2025-11-26
**Status:** Ready for Implementation
**Estimated Time:** 30-45 minutes
