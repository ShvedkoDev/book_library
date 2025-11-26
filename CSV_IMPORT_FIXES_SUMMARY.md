# CSV Import Fixes Summary

## Date: 2025-11-26

## Issues Fixed

### 1. Character Encoding Problems ✅
**Problem:** CSV contains Windows-1252 encoded characters (en-dash `\x96`, em-dash `\x97`, smart quotes) but database expects UTF-8
**Solution:** Added `cleanTextEncoding()` method that converts Windows-1252 characters to UTF-8 using byte sequences
**File:** `app/Services/BookCsvImportService.php:1025`

### 2. Invalid Integer Values ✅
**Problem:** Fields like "n/a", "unknown", "unavailable" in integer columns like `pages`
**Solution:** Added `cleanIntegerField()` method that converts non-numeric values to `null`
**File:** `app/Services/BookCsvImportService.php:1075`

### 3. File Permissions ✅
**Problem:** `BookIdentifier.php` had `600` permissions (permission denied errors)
**Solution:** Changed to `644` permissions
**Command:** `chmod 644 app/Models/BookIdentifier.php`

### 4. Duplicate PALM Codes ✅
**Problem:** Multiple books with `palm_code = "unavailable"` violating unique constraint
**Solution:** Convert "unavailable" palm_code to `null` to avoid duplicates
**File:** `app/Services/BookCsvImportService.php:546`

### 5. UTF-8 Encoding in Error Logs ✅
**Problem:** Malformed UTF-8 in error messages causing JSON encoding failures
**Solution:** Clean error messages with `mb_convert_encoding()` before storing
**File:** `app/Models/CsvImport.php:175`

### 6. Array Casting for Error Logs ✅
**Problem:** `error_log` field not cast to array, causing storage issues
**Solution:** Added `error_log` to array casts in model
**File:** `app/Models/CsvImport.php:45`

## New Features

### Cleanup Command - HARD RESET
**Purpose:** Complete hard reset of ALL library data including lookup tables

**Usage:**
```bash
# Interactive confirmation
docker-compose exec app php artisan books:reset

# Force without confirmation
docker-compose exec app php artisan books:reset --force
```

**What It Deletes (EVERYTHING):**
- Books and all related data (files, keywords, classifications, etc.)
- Collections, Publishers, Creators
- CSV imports and data quality issues
- Analytics (views, downloads, searches)
- User interactions (ratings, reviews, bookmarks)
- **Classification values** (genres, purposes, types, etc.)
- **Classification types** (Purpose, Genre, Type, etc.)
- **Languages**
- **Geographic locations**

**What It Preserves:**
- Users and profiles only

**⚠️ IMPORTANT:** After hard reset, you MUST run `php artisan db:seed` before importing books OR just import directly from CSV which will auto-create all needed data.

## CSV Import Auto-Create Feature ✨

The CSV import now automatically creates missing:
- **Classification values** - New genres, purposes, types are created on-the-fly
- **Languages** - Languages not in the database are added automatically
- **Geographic locations** - Islands and states are created as needed
- **Collections** - Book collections are created if missing
- **Publishers** - Publisher organizations are auto-created
- **Creators** - Authors, illustrators, etc. are added automatically

This means you can do a complete hard reset and import directly without needing to seed first!

## Testing

### Option 1: Hard Reset + Direct Import (Recommended)
```bash
# Complete wipe
docker-compose exec app php artisan books:reset --force

# Import CSV - it will create everything automatically
# Navigate to /admin/csv-import and upload your CSV
```

### Option 2: Hard Reset + Seed + Import
```bash
# Complete wipe
docker-compose exec app php artisan books:reset --force

# Reseed base data
docker-compose exec app php artisan db:seed

# Import CSV via admin panel
# Navigate to /admin/csv-import
```

**Import Settings:**
- Navigate to `/admin/csv-import`
- Upload your CSV file
- Select import mode (usually "Upsert")
- Enable "Create Missing Relations" ✅
- Click "Import CSV"

## Expected Results

- Books with valid data should import successfully
- Books with `palm_code = "unavailable"` will have `null` palm_code
- Character encoding issues (dashes, quotes) automatically fixed
- Non-numeric page numbers converted to `null`
- Error messages properly logged without UTF-8 issues

## Files Modified

1. `app/Services/BookCsvImportService.php` - Added encoding/integer cleaning, palm_code handling
2. `app/Models/CsvImport.php` - Fixed array casting and UTF-8 cleaning
3. `app/Console/Commands/ResetBookData.php` - Enhanced cleanup command
4. File permissions on `app/Models/BookIdentifier.php`

## Next Steps

1. Test import with full CSV file
2. Verify all books import correctly
3. Check that character encoding is preserved
4. Validate relationships (publishers, creators, collections) are created
