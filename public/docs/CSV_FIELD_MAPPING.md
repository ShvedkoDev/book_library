# CSV Field Mapping Guide

## Pipe Separator (|) - Multi-Value Fields

The book library CSV import system uses the **pipe character (|)** as a separator for fields that can contain multiple values. This allows you to store multiple items (authors, languages, keywords, etc.) in a single CSV cell.

### Configuration
- **Separator**: `|` (pipe character)
- **Trimming**: All values are automatically trimmed of leading/trailing whitespace
- **Can be used in**: ANY column that accepts multiple items

## Fields Supporting Pipe-Separated Values

### Authors and Creators
Fields that support pipe-separated author names:
- **Author** (Author 1)
- **Author2**
- **Author3**
- **Other creator**
- **Other creator2**
- **Illustrator** through **Illustrator5**

**Example**:
```
lastname1, firstname1|lastname2, firstname2|lastname3, firstname3
```

This will create THREE separate author records for a single book.

### Languages
- **Language 1** (Primary Language)
- **Language 2** (Secondary Language)

**Example**:
```
Yapese|Pohnpeian|Chuukese|English
```

This will link the book to all four languages.

### Classifications
All classification fields support multiple values:
- **Purpose**
- **Genre**
- **Sub-genre**
- **Type**
- **Themes/Uses**
- **Learner level**

**Example**:
```
Fiction|Fantasy|Adventure
```

### Geographic Locations
- **Island** - Multiple island names separated by pipes
- **State** - Multiple state names separated by pipes

**Example**:
```
Chuuk|Pohnpei|Kosrae|Yap
```

### Keywords
- **Keywords** - Multiple keywords separated by pipes

**Example**:
```
education|reading|culture|history|traditions
```

### Files
Media files that can have multiple entries:
- **Coupled audio** - Multiple audio file names
- **Coupled video** - Multiple video URLs

**Example (Audio)**:
```
chapter1.mp3|chapter2.mp3|chapter3.mp3
```

**Example (Video)**:
```
https://example.com/video1.mp4|https://example.com/video2.mp4
```

### Book Relationships
These fields contain relationship codes that link books together:
- **Related (same)** - Same version in different formats
- **Related (omnibus)** - Omnibus/collection versions
- **Related (support)** - Supporting materials
- **Related (translated)** - Translations with identical titles

**Example**:
```
YSCI001|YSCI002|YSCI003
```

All books with the same relationship code will be automatically linked together.

## Single-Value Fields (NO Pipe Separator)

These fields should contain only a single value:
- **ID** - Internal identifier
- **PALM code** - PALM code identifier
- **Title** - Book title
- **Sub-title** - Book subtitle
- **Translated-title** - Translation of the title
- **Physical type** - Format (Book, eBook, Audio, etc.)
- **Year** - Publication year
- **Pages** - Page count
- **TOC** - Table of contents
- **DESCRIPTION** - Book description
- **ABSTRACT** - Book abstract
- **VLA standard** - VLA standard reference
- **VLA benchmark** - VLA benchmark reference
- **UPLOADED** - Access level (Y/N/L)
- **Collection** - Collection name (single)
- **Publisher** - Publisher name (single)
- **Contributor / Project / Partner** - Program name (single)

## Format Examples

### Complete CSV Row Example
```csv
ID,Title,Author,Language 1,Language 2,Genre,Keywords,Island
P001,"My Book","Smith, John|Jones, Mary|Davis, Sarah","English","Chuukese|Pohnpeian","Fiction|Adventure","education|culture|reading","Chuuk|Pohnpei"
```

### Breakdown
- **ID**: P001
- **Title**: My Book
- **Author**: Three authors (Smith John, Jones Mary, Davis Sarah)
- **Language 1**: English
- **Language 2**: Two languages (Chuukese and Pohnpeian)
- **Genre**: Two genres (Fiction and Adventure)
- **Keywords**: Three keywords
- **Island**: Two islands

### Another Example - Multiple Languages Per Book
```csv
ID,Title,Primary Language,Secondary Language
B001,"Bilingual Story","English|Spanish","French|German"
```

This creates a book with:
- 2 primary languages (English, Spanish)
- 2 secondary languages (French, German)

## Important Rules

1. **Separator Character**: Always use the pipe (`|`) character to separate multiple values
2. **No Spaces Around Pipe**: Values are automatically trimmed, so `value1|value2` and `value1 | value2` are equivalent
3. **Empty Values**: Empty values between separators are ignored (`value1||value2` = `value1|value2`)
4. **Single Values**: Fields that support multi-values work fine with just one value (`single_value`)
5. **Case Sensitivity**: Most fields are case-insensitive (e.g., languages, genres), but author names ARE case-sensitive
6. **Special Characters**: Other special characters are preserved as-is (only pipes are treated as separators)

## Field Mapping Reference

| CSV Column | Maps To | Type | Multi-Value |
|-----------|---------|------|------------|
| ID | internal_id | string | No |
| PALM code | palm_code | string | No |
| Title | title | string | No |
| Sub-title | subtitle | string | No |
| Translated-title | translated_title | string | No |
| Physical type | physical_type | string | No |
| Year | publication_year | integer | No |
| Pages | pages | integer | No |
| TOC | toc | text | No |
| Notes related to the issue. | notes_issue | text | No |
| Notes related to version. | notes_version | text | No |
| Notes related to content. | notes_content | text | No |
| DESCRIPTION | description | text | No |
| ABSTRACT | abstract | text | No |
| VLA standard | vla_standard | string | No |
| VLA benchmark | vla_benchmark | string | No |
| CONTACT | contact | string | No |
| UPLOADED | access_level | enum (Y/N/L) | No |
| Collection | collection | string | No |
| Publisher | publisher | string | No |
| Contributor / Project / Partner | publisher_program | string | No |
| Language 1 | primary_language | string | **YES** |
| ISO (Language 1) | primary_language_iso | string | No |
| Language 2 | secondary_language | string | **YES** |
| ISO (Language 2) | secondary_language_iso | string | No |
| Island | geographic_island | string | **YES** |
| State | geographic_state | string | **YES** |
| Author | author_1 | string | **YES** |
| Author2 | author_2 | string | **YES** |
| Author3 | author_3 | string | **YES** |
| Other creator | other_creator_1 | string | **YES** |
| Other creator ROLE | other_creator_1_role | string | No |
| Other creator2 | other_creator_2 | string | **YES** |
| Other creator2 ROLE | other_creator_2_role | string | No |
| Illustrator | illustrator_1 | string | **YES** |
| Illustrator2 | illustrator_2 | string | **YES** |
| Illustrator3 | illustrator_3 | string | **YES** |
| Illustrator4 | illustrator_4 | string | **YES** |
| Illustrator5 | illustrator_5 | string | **YES** |
| Purpose | classification_purpose | string | **YES** |
| Genre | classification_genre | string | **YES** |
| Sub-genre | classification_subgenre | string | **YES** |
| Type | classification_type | string | **YES** |
| Themes/Uses | classification_themes | string | **YES** |
| Learner level | classification_learner_level | string | **YES** |
| Keywords | keywords | string | **YES** |
| Related (same) | related_same_version | string | **YES** |
| Related (omnibus) | related_omnibus | string | **YES** |
| Related (support) | related_supporting | string | **YES** |
| Related (translated) | related_translated | string | **YES** |
| DIGITAL SOURCE | digital_source | string | No |
| DOCUMENT FILENAME | pdf_filename | string | No |
| THUMBNAIL FILENAME | thumbnail_filename | string | No |
| ALTERNATIVE DOCUMENT FILENAME | pdf_filename_alt | string | No |
| ALTERNATIVE THUMBNAIL FILENAME | thumbnail_filename_alt | string | No |
| ALTERNATIVE DIGITAL SOURCE | digital_source_alt | string | No |
| Coupled audio | audio_files | string | **YES** |
| Coupled video | video_urls | string | **YES** |
| UH hard copy ref | uh_reference_number | string | No |
| UH hard copy link | uh_catalog_link | string | No |
| UH hard copy call number | uh_call_number | string | No |
| UH note | uh_notes | text | No |
| COM hard copy ref | com_reference_number | string | No |
| COM hard copy call number | com_call_number | string | No |
| COM hard copy ref NOTE | com_notes | text | No |
| Library link UH | library_link_uh | string | No |
| Library link UH alt. | library_link_uh_alt | string | No |
| Library link COM-FSM | library_link_com_fsm | string | No |
| Library link COM-FSM alt. | library_link_com_fsm_alt | string | No |
| Library link MARC | library_link_marc | string | No |
| Library link MARC alt. | library_link_marc_alt | string | No |
| Library link MICSEM | library_link_micsem | string | No |
| Library link MICSEM alt. | library_link_micsem_alt | string | No |
| Library link 5 | library_link_5 | string | No |
| Library link 5 alt. | library_link_5_alt | string | No |
| OLLC number | oclc_number | string | No |
| ISBN number | isbn_number | string | No |
| Other number | other_number | string | No |

## Tips for Success

1. **Test with a small sample** before importing large files
2. **Use consistent formatting** across all rows
3. **Verify author names** are correctly formatted (usually: "LastName, FirstName Middle")
4. **Check language spellings** for accuracy (Yapese, Pohnpeian, etc.)
5. **Validate relationships** before import to ensure codes are correct
6. **Review the validation report** carefully before confirming import

## Common Issues and Solutions

### Issue: Authors not being imported
- **Cause**: Missing pipe separator between author names
- **Solution**: Ensure you use `Author1|Author2|Author3` format

### Issue: Languages not linking to books
- **Cause**: Incorrect language names or whitespace
- **Solution**: Check language names and remove extra spaces (they're auto-trimmed but verify)

### Issue: Keywords appearing as single entry
- **Cause**: Using comma or semicolon instead of pipe separator
- **Solution**: Use pipe character (|) only

### Issue: Book relationships not being matched
- **Cause**: Inconsistent relationship codes
- **Solution**: Ensure all related books use identical codes (case-sensitive)

## Getting Help

If you encounter issues with CSV import:
1. Check the **Validation Report** in the admin panel
2. Review the **Import History** for specific error messages
3. Consult this **Field Mapping Guide** for proper formatting
4. Contact the system administrator for technical assistance

---

**Last Updated**: 2026-01-07
**System Version**: Book Library v1.0

