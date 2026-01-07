# CSV Import - Pipe Separator Implementation

## Overview

The Book Library CSV import system now fully supports the **pipe character (|)** as a separator for multi-value fields. This allows you to store multiple authors, languages, keywords, geographic locations, and other related items in a single CSV cell.

## Implementation Summary

### Configuration
- **Separator Character**: `|` (pipe)
- **Configuration File**: `config/csv-import.php`
- **Configuration Setting**: `'separator' => '|'`

### Code Changes

#### 1. **BookCsvImportRelationships.php** (Trait)
Updated methods to support pipe-separated values:

- **`attachLanguages()`** - Now supports pipe-separated language names in both primary and secondary language fields
  ```php
  // Before: Single language per field
  // After: "English|Spanish|French" creates 3 language associations
  ```

- **`attachCreators()`** - Authors, illustrators, and other creators now support pipe separation
  ```php
  // Before: One author per "Author" field
  // After: "lastname1, firstname1|lastname2, firstname2" creates multiple authors
  ```

- **Existing Methods** (already had pipe support):
  - `attachClassifications()` - For classifications (Purpose, Genre, etc.)
  - `attachGeographicLocations()` - For islands and states
  - `attachKeywords()` - For keywords
  - `attachFiles()` - For audio and video files
  - `attachBookRelationships()` - For relationship codes

#### 2. **CsvImport.php** (Filament Page)
Updated the file upload helper text to explain multi-value field usage with examples.

#### 3. **Public Documentation**
Created comprehensive documentation at `/docs/CSV_FIELD_MAPPING.md` with:
- Complete field mapping reference
- Multi-value field examples
- Format guidelines
- Common issues and solutions
- Best practices

## Supported Multi-Value Fields

### Authors and Creators
| Field | Column Name | Example |
|-------|------------|---------|
| Primary Author | Author | `Smith, John\|Jones, Mary\|Davis, Sarah` |
| Secondary Authors | Author2, Author3 | `Brown, Alice\|Green, Bob` |
| Illustrators | Illustrator, Illustrator2-5 | `Illustrator Name 1\|Illustrator Name 2` |
| Other Creators | Other creator, Other creator2 | `Editor Name\|Contributor Name` |

### Languages
| Field | Example |
|-------|---------|
| Primary Language | `English\|Spanish\|French` |
| Secondary Language | `Yapese\|Pohnpeian\|Chuukese` |

### Classifications
| Field | Example |
|-------|---------|
| Purpose | `Education\|Entertainment\|Reference` |
| Genre | `Fiction\|Adventure\|Fantasy` |
| Sub-genre | `Science Fiction\|Adventure` |
| Type | `Picture Book\|Novel\|Comic` |
| Themes/Uses | `Culture\|History\|Traditions` |
| Learner level | `Elementary\|Middle School\|High School` |

### Geographic Locations
| Field | Example |
|-------|---------|
| Island | `Chuuk\|Pohnpei\|Kosrae\|Yap` |
| State | `Chuuk State\|Pohnpei State` |

### Media and References
| Field | Example |
|-------|---------|
| Keywords | `education\|reading\|culture\|history` |
| Coupled audio | `chapter1.mp3\|chapter2.mp3\|chapter3.mp3` |
| Coupled video | `https://example.com/video1.mp4\|https://example.com/video2.mp4` |
| Related (same) | `YSCI001\|YSCI002\|YSCI003` |

## How It Works

### Processing Flow

1. **CSV Upload**: User uploads a CSV file with pipe-separated values
   ```csv
   ID,Title,Author,Language 1,Genre
   P001,"My Book","Smith, John|Jones, Mary","English|Spanish","Fiction|Adventure"
   ```

2. **Validation**: The system validates the file structure and content
   - Checks for required columns
   - Validates data types and formats
   - Warns about potential issues

3. **Import Processing**:
   - Reads each row and maps to database fields
   - For multi-value fields, calls `splitMultiValue()` method
   - `splitMultiValue()` splits by pipe separator and trims whitespace
   - Creates separate database records for each value

4. **Database Storage**:
   ```
   book_creators table:
   - book_id: 1, creator_id: 10, creator_type: 'author', sort_order: 0
   - book_id: 1, creator_id: 11, creator_type: 'author', sort_order: 1
   
   book_languages table:
   - book_id: 1, language_id: 5, is_primary: true
   - book_id: 1, language_id: 15, is_primary: true
   ```

### The `splitMultiValue()` Method

Located in `BookCsvImportRelationships.php`:

```php
protected function splitMultiValue(string $value): array
{
    if (empty($value)) {
        return [];
    }

    $values = explode($this->separator, $value);
    
    // Trim whitespace from each value and filter out empty strings
    return array_filter(array_map('trim', $values));
}
```

**Features:**
- Splits string by configured separator (`|`)
- Automatically trims leading/trailing whitespace from each value
- Filters out empty values (handles `value1||value2`)
- Returns array of cleaned values

## Admin Interface Features

### CSV Import Page (`/admin/csv-import`)

The import page now includes:

1. **File Upload Field**:
   - Accepts .csv, .txt files
   - Maximum 50MB
   - Clear instructions about pipe separator usage

2. **Helper Text**:
   ```
   Multi-value Fields: Use the pipe separator (|) to separate multiple items. Examples:
   • Authors: "lastname1, firstname1|lastname2, firstname2|lastname3"
   • Languages: "Yapese|Pohnpeian|Chuukese|English"
   • Keywords: "keyword1|keyword2|keyword3"
   • Islands/States: "Island1|Island2|Island3"
   The pipe separator can appear in ANY column EXCEPT single-value fields like Title, Subtitle, Year, etc.
   ```

3. **Documentation Link**:
   - "View Field Documentation" links to `/docs/CSV_FIELD_MAPPING.md`
   - Comprehensive guide with examples and troubleshooting

4. **Import Options**:
   - Mode selection (upsert, create_only, update_only, create_duplicates)
   - Create missing relations (auto-create authors, languages, etc.)
   - Skip invalid rows option

## Example CSV Files

### Simple Example
```csv
ID,Title,Author,Language 1
P001,"Basic Book","Smith, John","English"
P002,"Bilingual Story","Jones, Mary|Brown, Alice","English|Spanish"
```

### Complex Example
```csv
ID,Title,Author,Language 1,Language 2,Genre,Keywords,Island,Illustrator
P001,"My Adventure","Smith, John|Jones, Mary","English","Spanish","Fiction|Adventure","education|culture","Chuuk","Davis, Sarah|Green, Tom"
P002,"Learning Fun","Brown, Alice","English|Yapese","French|German","Educational","learning|fun|culture","Pohnpei","Wilson, Jane"
```

### Author Format
Authors should be formatted as: `LastName, FirstName MiddleInitial`

```csv
Smith, John Alexander|Jones, Mary Ellen|Brown, Samuel David
```

## Validation and Error Handling

### Validation Steps
1. File exists and is readable
2. File size within limits
3. CSV structure is valid
4. Required columns present
5. Data types are correct for each column

### Error Messages
- Missing required fields: "Row X: Missing required field 'Title'"
- Invalid data format: "Row X: Field 'publication_year' should be numeric"
- Header issues: "Missing required column: Author"

### Warnings (non-blocking)
- Column count mismatch
- Field length exceeds max length
- Invalid enum values (with suggestions)
- Validation limited to first 100 rows

## Configuration

The system configuration is in `config/csv-import.php`:

```php
'separator' => '|',
'alternative_separator' => ';',  // Currently unused
'batch_size' => 100,
'max_file_size' => 52428800,  // 50MB
```

### Related Configuration

**Field Mapping** (`field_mapping` array):
- Maps CSV column names to database fields
- Indicates which fields support multi-values
- Used for validation and processing

**Classification Type Mapping** (`classification_type_mapping`):
- Maps classification CSV fields to classification types
- Allows dynamic classification creation

**Relationship Type Mapping** (`relationship_type_mapping`):
- Maps relationship CSV fields to relationship types
- Used for book relationship linking

## Database Impact

### New Records Created
When importing with pipe-separated values:

```
Book: P001 "My Book"
  ↓ Author field: "Smith, John|Jones, Mary"
  ├─ book_creators (Smith, John as author)
  └─ book_creators (Jones, Mary as author)
  
  ↓ Language field: "English|Spanish"
  ├─ book_languages (English, primary)
  └─ book_languages (Spanish, primary)
```

### Storage Efficiency
- Instead of creating separate CSV columns for each author/language
- One field can hold unlimited pipe-separated values
- Reduces CSV column count
- Makes data entry easier for multi-value fields

## Performance Considerations

### Batch Processing
- Imports process in batches (default: 100 rows per batch)
- Each batch wrapped in database transaction
- Automatic rollback on error

### Memory Usage
- Tracks memory usage during import
- Warns if usage exceeds thresholds
- Can be tuned via configuration

### Query Optimization
- Foreign key checks disabled during import (re-enabled after)
- Query log disabled to save memory
- Bulk operations used where possible

## Troubleshooting

### Pipe Separator Not Working
**Check:**
1. CSV is saved with UTF-8 encoding
2. You're using pipe `|` not other characters
3. No spaces before/after pipe (auto-trimmed, but verify)

### Authors Not Being Created
**Check:**
1. Author names are formatted correctly: "LastName, FirstName"
2. Multiple authors separated by pipe: "Author1|Author2"
3. "Create missing relations" option is enabled

### Languages Not Linking
**Check:**
1. Language names match database (Yapese, not Yap; English, not english)
2. Languages separated by pipe: "Language1|Language2"
3. Languages table has the required language records

### Keywords Creating Single Entry
**Check:**
1. Using pipe separator, not comma or semicolon
2. Format: "keyword1|keyword2|keyword3"
3. No leading/trailing spaces in keywords

## Testing the Implementation

### Manual Testing
1. Go to `/admin/csv-import`
2. Create test CSV with pipe-separated values
3. Upload and validate
4. Review validation report
5. Perform import
6. Verify in book details that all items were created

### Example Test CSV
```csv
ID,Title,Author,Language 1,Genre,Keywords
TEST001,"Test Multi-Value Book","Smith, John|Jones, Mary","English|Spanish","Fiction|Adventure","test|sample|pipe-separator"
```

**Expected Results:**
- 2 authors created (Smith, John; Jones, Mary)
- 2 languages linked (English, Spanish)
- 2 genres linked (Fiction, Adventure)
- 3 keywords created (test, sample, pipe-separator)

## Future Enhancements

Potential improvements:
1. Alternative separator support (configuration option)
2. Quoted values support for pipe characters in content
3. Escape sequence support (e.g., `\|` for literal pipe)
4. Custom delimiter per field type
5. UI preview showing how pipe-separated values will be split

## References

- **Configuration**: `config/csv-import.php`
- **Service Class**: `app/Services/BookCsvImportService.php`
- **Relationship Trait**: `app/Services/BookCsvImportRelationships.php`
- **Filament Page**: `app/Filament/Pages/CsvImport.php`
- **Documentation**: `public/docs/CSV_FIELD_MAPPING.md`
- **Admin URL**: `/admin/csv-import`

## Support

For issues or questions about CSV import:
1. Check `/docs/CSV_FIELD_MAPPING.md` for field reference
2. Review Import History in admin for error messages
3. Check application logs: `storage/logs/laravel.log`
4. Contact system administrator

---

**Implementation Date**: January 7, 2026
**System**: Book Library v1.0
**Last Updated**: January 7, 2026

