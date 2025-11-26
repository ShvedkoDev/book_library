# Book Files Auto-Import System

## Overview

Automatically matches and attaches PDF and PNG files from the `NEW-BATCH` folder to existing books in the database based on filename patterns.

## Filename Pattern Analysis

### Pattern Structure
```
PALM [Collection Type] - [LANGUAGE] - [Title].[extension]
```

### Collection Type Mapping

| Filename Pattern | Database Collection |
|-----------------|---------------------|
| `PALM CD - ...` | PALM CD |
| `PALM - Printed [Trial version] - ...` | PALM trial |
| `PALM - Printed - ...` | PALM final |

### Language Mapping

Languages are extracted from the filename and matched to existing `languages` table entries:
- CHUUKESE → Chuukese
- YAPESE → Yapese
- POHNPEIAN → Pohnpeian
- KOSRAEAN → Kosraean
- ULITHIAN → Ulithian
- WOLEAIAN → Woleaian

### Title Matching Algorithm

The seeder uses fuzzy matching to find the best book match:

1. **Language Filter**: Only considers books in the matching language
2. **Title Similarity**: Calculates similarity score (0-1) using `similar_text()`
3. **Collection Bonus**: +0.2 score if collection also matches
4. **Threshold**: Minimum 60% similarity required for a match

## File Structure

```
NEW-BATCH/
├── PDF/
│   ├── PALM CD - Chuukese - Anapet me ewe chóón nááng.pdf
│   ├── PALM - Printed - YAPESE - Beaq Ni Ba Moqon Ngea Ba Raanʻ I Moongkii.pdf
│   └── ...
└── PNG/
    ├── PALM CD - Chuukese - Anapet me ewe chóón nááng.png
    ├── PALM - Printed - YAPESE - Beaq Ni Ba Moqon Ngea Ba Raanʻ I Moongkii.png
    └── ...
```

## Usage

### Step 1: Place Files in NEW-BATCH Folder

Upload your PDF and PNG files to the `NEW-BATCH` folder:

```bash
# Create directories if they don't exist
mkdir -p NEW-BATCH/PDF
mkdir -p NEW-BATCH/PNG

# Upload files (via FTP, rsync, or copy)
cp /path/to/files/*.pdf NEW-BATCH/PDF/
cp /path/to/files/*.png NEW-BATCH/PNG/
```

### Step 2: Move Files to Storage

Move files to the Laravel storage folder:

```bash
# Move PDFs and PNGs to the books folder
docker-compose exec app bash -c "cp NEW-BATCH/PDF/* storage/app/public/books/"
docker-compose exec app bash -c "cp NEW-BATCH/PNG/* storage/app/public/books/"

# Create symlink if not exists
docker-compose exec app php artisan storage:link
```

**Note**: Both PDFs and PNGs go into the same `storage/app/public/books/` directory. The seeder will correctly identify file types and create appropriate database records.

### Step 3: Run the Seeder

```bash
# Run the book files seeder
docker-compose exec app php artisan db:seed --class=BookFilesSeeder
```

### Output Example

```
🔍 Scanning NEW-BATCH folders for files...
📄 Found 29 PDF files
🖼️  Found 29 PNG files
  ✓ Matched: Anapet me ewe cho?o?n na?a?ng
  ✓ Matched: Beaq ni ba moqon ngea ba raan' i moongkii
  ✓ Matched: Cheeriei! Cheeriei!
  ...

✅ Successfully matched 29 books
```

## Matching Algorithm Details

### Title Cleaning

Before matching, titles are cleaned:
- Remove special characters: `[^\p{L}\p{N}\s]`
- Normalize spaces
- Convert to lowercase

### Similarity Calculation

```php
// Example
$fileTitle = "Beaq Ni Ba Moqon Ngea Ba Raanʻ I Moongkii"
$bookTitle = "Beaq ni ba moqon ngea ba raan' i moongkii"

similar_text($clean1, $clean2, $percent);
// $percent = 95.5 (high similarity)
```

### Collection Bonus

```php
$score = 0.85; // Title similarity
if (collection_matches) {
    $score += 0.2; // Now 1.05 (but capped at 1.0)
}
```

## Database Changes

The seeder creates `book_files` records:

```sql
INSERT INTO book_files (
    book_id,
    file_type,      -- 'pdf' or 'thumbnail'
    file_path,      -- 'books/filename.pdf' or 'books/filename.png'
    filename,       -- 'filename.pdf' or 'filename.png'
    mime_type,      -- 'application/pdf' or 'image/png'
    is_primary,     -- true
    digital_source, -- 'Auto-matched from NEW-BATCH import'
    is_active,      -- true
    sort_order      -- 0
) VALUES ...
```

## Edge Cases Handled

### 1. Duplicate Prevention
- Checks if file already attached before creating
- Shows "(Files already attached, skipping)" message

### 2. Missing PNG Files
- PDF can be attached without corresponding PNG
- PNG is optional

### 3. No Match Found
- Lists unmatched files at the end
- Shows up to 10 unmatched files with "... and N more"

### 4. Case Sensitivity
- Handles both `.pdf` and `.PDF` extensions
- Language names are case-insensitive

### 5. Special Characters
- Handles Unicode characters in filenames (ʻ, ó, á, etc.)
- Normalizes for matching

## Test Results

**Current Performance:**
- 29 out of 29 files matched (100% success rate)
- 0 unmatched files
- Fuzzy matching handles:
  - Case differences
  - Character encoding variations
  - Minor spelling differences

## Troubleshooting

### No Matches Found

If books aren't matching:

1. **Check Language**: Ensure language exists in database
   ```bash
   docker-compose exec app php artisan tinker --execute="
   \App\Models\Language::all(['name', 'code'])->each(fn(\$l) => echo \$l->name . ' (' . \$l->code . ')' . PHP_EOL);
   "
   ```

2. **Check Title Similarity**: Titles must be at least 60% similar
   ```bash
   # Manually test similarity
   docker-compose exec app php artisan tinker --execute="
   \$file = 'Title from filename';
   \$book = \App\Models\Book::find(1)->title;
   similar_text(strtolower(\$file), strtolower(\$book), \$percent);
   echo \$percent . '%';
   "
   ```

3. **Check Collection**: Verify collection names match expectations

### Files Already Exist

The seeder skips files that are already attached. To re-import:

```bash
# Remove existing book_files records
docker-compose exec app php artisan tinker --execute="
\App\Models\BookFile::where('digital_source', 'LIKE', '%Auto-matched%')->delete();
"

# Re-run seeder
docker-compose exec app php artisan db:seed --class=BookFilesSeeder
```

## Future Enhancements

Potential improvements:

1. **Metadata Extraction**: Extract PALM codes or other metadata from filenames
2. **Duplicate Detection**: Check for files with same content (hash-based)
3. **Batch Processing**: Process large file sets in chunks
4. **Manual Override**: Allow manual mapping for edge cases
5. **File Validation**: Verify PDF/PNG integrity before attaching

## Files Modified

- `database/seeders/BookFilesSeeder.php` - Main seeder class
- `docs/BOOK_FILES_AUTO_IMPORT.md` - This documentation

## Related Documentation

- `CSV_IMPORT_FIXES_SUMMARY.md` - CSV import system
- `DATABASE_UPDATE_GUIDE_2025_11_26.md` - Database schema changes
