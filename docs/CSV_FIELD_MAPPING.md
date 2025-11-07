# CSV Field Mapping Documentation

## Overview
This document defines the complete CSV structure for importing and exporting books in the Micronesian Teachers Digital Library. The mapping is based on the existing `test.csv` structure and database schema.

---

## CSV Structure

### Header Rows
The CSV file uses **TWO header rows**:
1. **Row 1**: Human-readable column names
2. **Row 2**: Database field mapping (technical reference)
3. **Row 3+**: Actual data

---

## Complete Column List

### 1. IDENTIFIERS

#### ID
- **CSV Header**: `ID`
- **Database**: `books.internal_id`
- **Type**: String (max 50 chars)
- **Required**: Yes (for updates), Auto-generated (for new)
- **Unique**: Yes
- **Format**: `P###-a` (e.g., `P001-a`, `P026-a`)
- **Description**: Internal unique identifier for the book
- **Example**: `P001-a`

#### PALM code
- **CSV Header**: `PALM code`
- **Database**: `books.palm_code`
- **Type**: String (max 100 chars)
- **Required**: No
- **Unique**: Yes
- **Format**: Alphanumeric code (e.g., `TAW14`, `KNT1`, `PoOJ22`)
- **Description**: PALM project code (if applicable)
- **Example**: `TAW14`
- **Note**: Use `unavailable` if no PALM code exists

---

### 2. CORE BOOK INFORMATION

#### Title
- **CSV Header**: `Title`
- **Database**: `books.title`
- **Type**: String (max 500 chars)
- **Required**: Yes
- **Example**: `A?a?n a?tin mwa?a?n we pikinik`
- **Note**: Include special characters exactly as they appear

#### Sub-title
- **CSV Header**: `Sub-title`
- **Database**: `books.subtitle`
- **Type**: String (max 500 chars)
- **Required**: No
- **Example**: `(trial version)`

#### Translated-title
- **CSV Header**: `Translated-title`
- **Database**: `books.translated_title`
- **Type**: String (max 500 chars)
- **Required**: No
- **Format**: Usually in square brackets
- **Example**: `[Boys at the picnic]`

#### Physical type
- **CSV Header**: `Physical type`
- **Database**: `books.physical_type`
- **Type**: Enum
- **Required**: No
- **Values**: `book`, `journal`, `magazine`, `workbook`, `poster`, `other`, `Booklet`
- **Example**: `Booklet`
- **Note**: Case-insensitive, will be normalized to lowercase

#### Year
- **CSV Header**: `Year`
- **Database**: `books.publication_year`
- **Type**: Integer
- **Required**: No
- **Range**: 1900-2100
- **Example**: `1979`
- **Note**: Question marks (e.g., `1978?`) will be stripped

#### Pages
- **CSV Header**: `Pages`
- **Database**: `books.pages`
- **Type**: Integer
- **Required**: No
- **Example**: `8`

---

### 3. CLASSIFICATIONS (Multi-Value Fields)

**Note**: All classification fields support multiple values separated by pipe `|`

#### Purpose
- **CSV Header**: `Purpose`
- **Database**: `classification_values.value` (type: `purpose`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Literacy Development`
- **Multiple Values**: `Literacy Development|Science Education|Mathematics`

#### Genre
- **CSV Header**: `Genre`
- **Database**: `classification_values.value` (type: `genre`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Readers`
- **Multiple Values**: `Readers|Fiction|Poetry`

#### Sub-genre
- **CSV Header**: `Sub-genre`
- **Database**: `classification_values.value` (type: `sub-genre`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Determine by level`
- **Multiple Values**: `Instructional|Recreational|Reference`

#### Type
- **CSV Header**: `Type`
- **Database**: `classification_values.value` (type: `type`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Instructional`
- **Multiple Values**: `Instructional|Assessment|Teacher Resource`

#### Themes/Uses
- **CSV Header**: `Themes/Uses`
- **Database**: `classification_values.value` (type: `themes-uses`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Determine topic`
- **Multiple Values**: `Language Arts|Social Studies|Science`

#### Learner level
- **CSV Header**: `Learner level`
- **Database**: `classification_values.value` (type: `learner-level`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `Grade 7`
- **Multiple Values**: `Grade 1|Grade 2|Kindergarten`

---

### 4. CREATORS (Multi-Value Fields)

**Note**: All creator fields can have multiple values. Creators are looked up by name or created if they don't exist.

#### Author
- **CSV Header**: `Author`
- **Database**: `creators.name` (type: `author`)
- **Type**: String
- **Required**: No
- **Format**: `Last, First` or `First Last`
- **Example**: `William, Alvios`

#### Author2, Author3
- **CSV Headers**: `Author2`, `Author3`
- **Database**: `creators.name` (type: `author`)
- **Type**: String
- **Required**: No
- **Note**: Additional authors in order

#### Other creator
- **CSV Header**: `Other creator`
- **Database**: `creators.name` (type: varies)
- **Type**: String
- **Required**: No
- **Example**: `Johnny, Oliver`
- **Note**: Must be paired with `Other creator ROLE`

#### Other creator ROLE
- **CSV Header**: `Other creator ROLE`
- **Database**: `book_creators.role_description`
- **Type**: String
- **Required**: If `Other creator` is provided
- **Example**: `translated by`, `assisted by`, `edited by`
- **Common Values**: `translated by`, `assisted by`, `edited by`, `compiled by`, `adapted by`

#### Other creator2, Other creator2 ROLE
- **CSV Headers**: `Other creator2`, `Other creator2 ROLE`
- **Database**: Same as above
- **Type**: String
- **Required**: No
- **Note**: Second "other creator" with role

#### Illustrator, Illustrator2, Illustrator3, Illustrator4, Illustrator5
- **CSV Headers**: `Illustrator`, `Illustrator2`, `Illustrator3`, `Illustrator4`, `Illustrator5`
- **Database**: `creators.name` (type: `illustrator`)
- **Type**: String
- **Required**: No
- **Example**: `Layne, Chris`
- **Note**: Multiple illustrators supported (up to 5 in current CSV)

---

### 5. PUBLISHING INFORMATION

#### Publisher
- **CSV Header**: `Publisher`
- **Database**: `publishers.name`
- **Type**: String
- **Required**: No
- **Example**: `UH Social Science Research Institute (SSRI), University of Hawaii at Manoa`
- **Note**: Publisher will be looked up by name or created if missing

#### Contributor / Project / Partner
- **CSV Header**: `Contributor / Project / Partner`
- **Database**: `publishers.program_name`
- **Type**: String
- **Required**: No
- **Example**: `Pacific Area Language Materials Development Center`
- **Note**: Stored as program name for the publisher

#### Collection
- **CSV Header**: `Collection`
- **Database**: `collections.name`
- **Type**: String
- **Required**: No
- **Example**: `PALM trial`
- **Note**: Collection will be looked up by name or created if missing

---

### 6. LANGUAGES (Multi-Value Fields)

#### Language 1
- **CSV Header**: `Language 1`
- **Database**: `languages.name` (primary language)
- **Type**: String
- **Required**: Highly recommended
- **Example**: `Chuukese`
- **Note**: This is marked as the primary language

#### ISO (Language 1)
- **CSV Header**: `ISO (Language 1)`
- **Database**: `languages.code`
- **Type**: String (3-letter ISO 639-3 code)
- **Required**: No (but helpful)
- **Example**: `chk`

#### Language 2
- **CSV Header**: `Language 2`
- **Database**: `languages.name` (secondary language)
- **Type**: String
- **Required**: No
- **Example**: `English`
- **Note**: Additional language, not primary

#### ISO (Language 2)
- **CSV Header**: `ISO (Language 2)`
- **Database**: `languages.code`
- **Type**: String (3-letter ISO 639-3 code)
- **Required**: No
- **Example**: `eng`

**Note**: For more than 2 languages, use pipe-separated values in Language 1 column

---

### 7. GEOGRAPHIC LOCATIONS (Multi-Value Fields)

#### Island
- **CSV Header**: `Island`
- **Database**: `geographic_locations.name`
- **Type**: String
- **Required**: No
- **Example**: `Chuuk Lagoon`
- **Multiple Values**: Pipe-separated (e.g., `Chuuk Lagoon|Weno`)

#### State
- **CSV Header**: `State`
- **Database**: `geographic_locations.name`
- **Type**: String
- **Required**: No
- **Example**: `Chuuk State`
- **Note**: Both Island and State are stored in same table, differentiated by parent relationships

---

### 8. EDUCATIONAL STANDARDS

#### VLA standard
- **CSV Header**: `VLA standard`
- **Database**: `books.vla_standard`
- **Type**: String (max 255 chars)
- **Required**: No
- **Example**: `Reading Comprehension`

#### VLA benchmark
- **CSV Header**: `VLA benchmark`
- **Database**: `books.vla_benchmark`
- **Type**: String (max 255 chars)
- **Required**: No
- **Example**: `Students will read and comprehend grade-level texts`

---

### 9. CONTENT DESCRIPTIONS

#### Keywords
- **CSV Header**: `Keywords`
- **Database**: `book_keywords.keyword`
- **Type**: String (pipe-separated)
- **Required**: No
- **Example**: `education|teaching|pacific islands|literacy`
- **Multiple Values**: Pipe-separated

#### TOC
- **CSV Header**: `TOC`
- **Database**: `books.toc`
- **Type**: Text (long)
- **Required**: No
- **Description**: Table of Contents
- **Example**: `Chapter 1: Introduction\nChapter 2: Main Content...`

#### ABSTRACT/DESCRIPTION
- **CSV Header**: `ABSTRACT/DESCRIPTION`
- **Database**: `books.description`
- **Type**: Text (long)
- **Required**: No
- **Description**: Full description or abstract of the book
- **Example**: `This book teaches children about...`

#### Notes related to the issue.
- **CSV Header**: `Notes related to the issue.`
- **Database**: `books.notes_issue`
- **Type**: Text
- **Required**: No
- **Description**: Notes about publication issues (printing, availability, etc.)
- **Example**: `This title is not included on the PALM CD-ROM (1999).`

#### Notes related to content.
- **CSV Header**: `Notes related to content.`
- **Database**: `books.notes_content`
- **Type**: Text
- **Required**: No
- **Description**: Notes about the content itself
- **Example**: `Content focuses on traditional fishing methods`

---

### 10. BOOK RELATIONSHIPS (Multi-Value Fields)

**Note**: All relationship fields use pipe-separated internal IDs

#### Related (same)
- **CSV Header**: `Related (same)`
- **Database**: `book_relationships.relationship_code` (type: `same_version`)
- **Type**: String (pipe-separated internal IDs)
- **Required**: No
- **Format**: Internal ID(s) like `P001`
- **Example**: `P001`
- **Multiple Values**: `P001|P002|P003`
- **Description**: Same version/edition of this book

#### Related (omnibus)
- **CSV Header**: `Related (omnibus)`
- **Database**: `book_relationships.relationship_code` (type: specific)
- **Type**: String (pipe-separated internal IDs)
- **Required**: No
- **Description**: Omnibus/collection containing this book

#### Related (support)
- **CSV Header**: `Related (support)`
- **Database**: `book_relationships.relationship_code` (type: `supporting`)
- **Type**: String (pipe-separated internal IDs)
- **Required**: No
- **Example**: `P010|P011`
- **Description**: Supporting materials (teacher guides, workbooks, etc.)

#### Related (same title, different language, or similar)
- **CSV Header**: `Related (same title, different language, or similar)`
- **Database**: `book_relationships.relationship_type` (type: `other_language` or custom)
- **Type**: String (pipe-separated internal IDs)
- **Required**: No
- **Example**: `P001-b|P001-c`
- **Description**: Same title in different languages or similar books
- **Special Note**: `**if translated title is identical, display together*`

---

### 11. FILE REFERENCES

#### UPLOADED
- **CSV Header**: `UPLOADED`
- **Database**: `books.access_level`
- **Type**: Enum
- **Required**: Yes
- **Values**: `Y` (full access), `N` (unavailable), `L` (limited)
- **Mapping**:
  - `Y` → `full`
  - `N` → `unavailable`
  - `L` → `limited`
- **Example**: `Y`

#### DIGITAL SOURCE (WHERE IS THE PDF FROM)
- **CSV Header**: `DIGITAL SOURCE (WHERE IS THE PDF FROM)`
- **Database**: `book_files.digital_source`
- **Type**: Text
- **Required**: No (but recommended if PDF exists)
- **Example**: `Downloaded from UH Scholar Space (https://hdl.handle.net/10125/42190) by iREi (2025).`

#### DOCUMENT FILENAME
- **CSV Header**: `DOCUMENT FILENAME`
- **Database**: `book_files.filename` (type: `pdf`, primary)
- **Type**: String (filename with extension)
- **Required**: If UPLOADED = Y
- **Path**: Can be full path or just filename
- **Example**: `PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik`
- **Note**: Extension (.pdf) may be omitted or included

#### THUMBNAIL FILENAME
- **CSV Header**: `THUMBNAIL FILENAME`
- **Database**: `book_files.filename` (type: `thumbnail`, primary)
- **Type**: String (filename with extension)
- **Required**: If UPLOADED = Y
- **Example**: `PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik`
- **Note**: Extension (.png, .jpg) may be omitted or included

#### ALTERNATIVE DOCUMENT FILENAME
- **CSV Header**: `ALTERNATIVE DOCUMENT FILENAME`
- **Database**: `book_files.filename` (type: `pdf`, non-primary)
- **Type**: String
- **Required**: No
- **Description**: Alternative version of the PDF

#### ALTERNATIVE THUMBNAIL FILENAME
- **CSV Header**: `ALTERNATIVE THUMBNAIL FILENAME`
- **Database**: `book_files.filename` (type: `thumbnail`, non-primary)
- **Type**: String
- **Required**: No

#### ALTERNATIVE DIGITAL SOURCE
- **CSV Header**: `ALTERNATIVE DIGITAL SOURCE (WHERE IS THE PDF FROM)`
- **Database**: `book_files.digital_source` (for alternative files)
- **Type**: Text
- **Required**: No

#### Coupled audio
- **CSV Header**: `Coupled audio`
- **Database**: `book_files.filename` or `book_files.external_url` (type: `audio`)
- **Type**: String (pipe-separated)
- **Required**: No
- **Format**: Filename or URL
- **Example**: `audio_file_1.mp3|audio_file_2.mp3`

#### Coupled video
- **CSV Header**: `Coupled video`
- **Database**: `book_files.external_url` (type: `video`)
- **Type**: String (URL, pipe-separated)
- **Required**: No
- **Format**: Usually YouTube or external URL
- **Example**: `https://youtube.com/watch?v=xxxxx`

---

### 12. CONTACT & ORDERING

#### CONTACT
- **CSV Header**: `CONTACT`
- **Database**: `books.contact`
- **Type**: Text
- **Required**: No
- **Description**: Contact information for ordering hard copies
- **Example**: `Contact UH SSRI at ssri@hawaii.edu for hard copies`

---

### 13. LIBRARY REFERENCES

#### UH hard copy ref
- **CSV Header**: `UH hard copy ref`
- **Database**: `library_references.reference_number` (library_code: `UH`)
- **Type**: String
- **Required**: No
- **Description**: University of Hawaii library reference number

#### UH hard copy link
- **CSV Header**: `UH hard copy link`
- **Database**: `library_references.catalog_link` (library_code: `UH`)
- **Type**: String (URL)
- **Required**: No

#### UH hard copy call number
- **CSV Header**: `UH hard copy call number`
- **Database**: `library_references.call_number` (library_code: `UH`)
- **Type**: String
- **Required**: No
- **Example**: `Pac.PL6252.K86K67 1979`

#### UH note
- **CSV Header**: `UH note`
- **Database**: `library_references.notes` (library_code: `UH`)
- **Type**: Text
- **Required**: No

#### COM hard copy ref
- **CSV Header**: `COM hard copy ref`
- **Database**: `library_references.reference_number` (library_code: `COM`)
- **Type**: String
- **Required**: No
- **Description**: College of Micronesia library reference

#### COM hard copy call number
- **CSV Header**: `COM hard copy call number`
- **Database**: `library_references.call_number` (library_code: `COM`)
- **Type**: String
- **Required**: No

#### COM hard copy ref NOTE
- **CSV Header**: `COM hard copy ref NOTE`
- **Database**: `library_references.notes` (library_code: `COM`)
- **Type**: Text
- **Required**: No

---

### 14. INTERNAL/METADATA FIELDS

#### Name match check
- **CSV Header**: `Name match check`
- **Database**: Not stored
- **Type**: Boolean (TRUE/FALSE)
- **Required**: No
- **Description**: Internal flag for CSV validation (not imported to database)
- **Example**: `TRUE`

---

## Multi-Value Field Separator

**Separator Character**: Pipe `|`

All fields that support multiple values use the pipe character as a separator:
- Authors: `Author1|Author2|Author3`
- Languages: `Chuukese|English|Pohnpeian`
- Classifications: `Literacy Development|Science Education`
- Keywords: `education|teaching|pacific islands`
- Related Books: `P001|P002|P003`
- Audio Files: `audio1.mp3|audio2.mp3`

**Escape Character**: Currently none (avoid using `|` in values themselves)

---

## Required vs Optional Fields

### Required for New Books:
- `Title`
- `ID` (can be auto-generated if blank)

### Highly Recommended:
- `Language 1` (Primary language)
- `Collection`
- `Publisher`
- `Year`
- `UPLOADED` (access level)

### Required if Conditional:
- `DOCUMENT FILENAME` - Required if `UPLOADED = Y`
- `THUMBNAIL FILENAME` - Required if `UPLOADED = Y`
- `Other creator ROLE` - Required if `Other creator` is provided

### All Other Fields: Optional

---

## Special Values & Constants

### Access Level Mapping:
- `Y` → `full` (full access, PDF available)
- `N` → `unavailable` (no PDF available)
- `L` → `limited` (partial access)

### PALM Code Special Values:
- `unavailable` - Used when no PALM code exists

### Physical Type Values:
- `book`, `journal`, `magazine`, `workbook`, `poster`, `other`, `Booklet`
- Case-insensitive, normalized to lowercase on import

---

## Data Type Summary

| Field Type | Data Type | Max Length | Example |
|------------|-----------|------------|---------|
| ID | String | 50 | `P001-a` |
| Title | String | 500 | `Title of Book` |
| Year | Integer | - | `1979` |
| Pages | Integer | - | `42` |
| Text Fields | Text | Unlimited | Long descriptions |
| Enums | String | Varies | `full`, `book`, `Y` |
| Multi-value | String | Varies | `Value1|Value2|Value3` |
| Boolean Flags | String | - | `TRUE`, `FALSE`, `Y`, `N` |

---

## Validation Rules

### String Length Limits:
- `internal_id`: Max 50 characters
- `palm_code`: Max 100 characters
- `title`, `subtitle`, `translated_title`: Max 500 characters
- `vla_standard`, `vla_benchmark`: Max 255 characters

### Integer Ranges:
- `publication_year`: Between 1900 and 2100
- `pages`: Minimum 1

### Unique Constraints:
- `internal_id` must be unique
- `palm_code` must be unique (if provided)

### Foreign Key Lookups:
- Collections: Looked up by name, created if `create_missing_relations` enabled
- Publishers: Looked up by name, created if enabled
- Languages: Must exist in database (seed first)
- Creators: Looked up by name, created if enabled
- Classification Values: Must exist in database (seed first)
- Geographic Locations: Looked up by name, created if enabled

---

## Import Behavior

### Create vs Update:
- **Match by**: `internal_id` (primary) or `palm_code` (secondary)
- **Create Mode**: If no match found and mode is `create_only` or `upsert`
- **Update Mode**: If match found and mode is `update_only` or `upsert`

### Relationship Handling:
- **Replace Strategy**: Removes existing relationships and creates new ones
- **Merge Strategy**: Keeps existing, adds new relationships

### Error Handling:
- Missing required fields: Skip row, log error
- Invalid foreign keys: Skip row or log warning depending on `create_missing_relations`
- Invalid enum values: Skip row, log error
- File not found: Log warning, continue import

---

## CSV File Format Requirements

### Encoding:
- **Character Encoding**: UTF-8
- **BOM**: Optional (UTF-8 BOM accepted)

### Delimiters:
- **Field Separator**: Comma `,`
- **Text Qualifier**: Double quote `"`
- **Escape Character**: Double quote `""` (for quotes within text)

### Line Endings:
- **Accepted**: `\n` (Unix), `\r\n` (Windows), `\r` (Mac)
- **Recommended**: `\n` (Unix style)

### Header Rows:
- **Row 1**: Human-readable column names (required)
- **Row 2**: Database mapping (optional, for reference)
- **Data starts**: Row 2 or 3 (depending on header structure)

---

## Special Handling Notes

### Special Characters:
- Glottal stops and special diacritics preserved: `A?a?n`, `Pohnpeian`
- Unicode characters supported
- Ensure UTF-8 encoding for proper display

### Empty Values:
- Empty cells treated as NULL
- Whitespace-only cells treated as empty
- `N/A`, `null`, `NULL` treated as NULL

### Date/Year Formats:
- Years with question marks (`1978?`) - strip question mark
- Invalid years logged as errors

### Boolean Conversion:
- `Y`, `y`, `yes`, `YES`, `true`, `TRUE`, `1` → true
- `N`, `n`, `no`, `NO`, `false`, `FALSE`, `0` → false

---

## Version History

- **Version 1.0** (2025-11-06): Initial documentation based on `test.csv` structure
- **Next**: Will be updated as import system is developed

---

**Document Maintainer**: Development Team
**Last Updated**: 2025-11-06
**Related Documents**: CSV_IMPORT_TODO.md, CSV template files
