# CSV Import/Export - Quick Reference

## 📋 Essential Information

### Required Fields
- **ID**: `P001-a` (unique identifier)
- **Title**: Book title

### Recommended Fields
- **Language 1**: `Chuukese`, `English`, etc.
- **Collection**: `PALM trial`
- **Publisher**: Publisher name
- **Year**: `1979`
- **UPLOADED**: `Y` (full), `N` (unavailable), `L` (limited)

---

## 🔤 Multi-Value Separator: Pipe `|`

Use **pipe character** to separate multiple values:

```csv
Grade 1|Grade 2|Grade 3
Smith, John|Doe, Jane
education|literacy|reading
P001|P002|P003
```

---

## 📊 Column Headers (First Row)

```
ID,Purpose,Genre,Sub-genre,Type,Themes/Uses,Keywords,Collection,
Physical type,Title,Sub-title,Translated-title,PALM code,
Related (same),Related (omnibus),Related (support),
Related (same title, different language, or similar),Year,
Author,Author2,Author3,Other creator,Other creator ROLE,
Other creator2,Other creator2 ROLE,Illustrator,Illustrator2,
Illustrator3,Illustrator4,Illustrator5,Publisher,
Contributor / Project / Partner,Pages,Language 1,ISO (Language 1),
Language 2,ISO (Language 2),Island,State,Learner level,
VLA standard,VLA benchmark,TOC,Notes related to the issue.,
Notes related to content.,ABSTRACT/DESCRIPTION,UPLOADED,
DIGITAL SOURCE (WHERE IS THE PDF FROM),DOCUMENT FILENAME,
THUMBNAIL FILENAME,Name match check,ALTERNATIVE DOCUMENT FILENAME,
ALTERNATIVE THUMBNAIL FILENAME,
ALTERNATIVE DIGITAL SOURCE (WHERE IS THE PDF FROM),
Coupled audio,Coupled video,CONTACT,UH hard copy ref,
UH hard copy link,UH hard copy call number,UH note,
COM hard copy ref,COM hard copy call number,COM hard copy ref NOTE
```

---

## 🔑 Key Field Mappings

| CSV Column | Database Field | Example |
|------------|---------------|---------|
| ID | books.internal_id | `P001-a` |
| Title | books.title | `Example Book` |
| PALM code | books.palm_code | `TAW14` |
| Year | books.publication_year | `1979` |
| Pages | books.pages | `42` |
| Physical type | books.physical_type | `book`, `workbook` |
| UPLOADED | books.access_level | `Y`→`full`, `N`→`unavailable` |
| Language 1 | languages.name (primary) | `Chuukese` |
| Collection | collections.name | `PALM trial` |
| Publisher | publishers.name | `University Press` |
| Author | creators.name (type: author) | `Smith, John` |
| Illustrator | creators.name (type: illustrator) | `Doe, Jane` |

---

## 👥 Creator Fields

### Basic Creators
- **Author, Author2, Author3**: Direct authors
- **Illustrator** (1-5): Illustrators
- **Other creator + Other creator ROLE**: Translators, editors, etc.

### Creator Roles (for "Other creator ROLE")
- `translated by`
- `edited by`
- `assisted by`
- `compiled by`
- `adapted by`

### Example
```csv
Author: "Smith, John"
Other creator: "Johnson, Mary"
Other creator ROLE: "translated by"
Illustrator: "Anderson, Bob"
```

---

## 📚 Classifications (Multi-Value)

All support pipe-separated values:

- **Purpose**: `Literacy Development|Science Education`
- **Genre**: `Readers|Fiction|Poetry`
- **Sub-genre**: `Early Readers|Chapter Books`
- **Type**: `Instructional|Reference`
- **Themes/Uses**: `Language Arts|Mathematics`
- **Learner level**: `Grade 1|Grade 2|Kindergarten`

---

## 🗺️ Geographic Locations

- **Island**: `Chuuk Lagoon|Weno`
- **State**: `Chuuk State`

Both stored in `geographic_locations` table

---

## 🌐 Languages

- **Language 1**: Primary language
- **ISO (Language 1)**: 3-letter code (e.g., `chk`)
- **Language 2**: Secondary language (optional)
- **ISO (Language 2)**: 3-letter code

### Common Languages
| Language | ISO 639-3 |
|----------|-----------|
| Chuukese | chk |
| Kosraean | kos |
| Yapese | yap |
| Pohnpeian | pon |
| English | eng |

---

## 📁 File References

### Required if UPLOADED = Y:
- **DOCUMENT FILENAME**: `example-book.pdf`
- **THUMBNAIL FILENAME**: `example-book-thumbnail.png`

### Optional:
- **DIGITAL SOURCE**: Where PDF came from
- **ALTERNATIVE DOCUMENT FILENAME**: Alternative PDF
- **Coupled audio**: `audio1.mp3|audio2.mp3`
- **Coupled video**: YouTube or video URL

### File Paths
Files should be uploaded to:
- PDFs: `/storage/app/public/books/pdfs/`
- Thumbnails: `/storage/app/public/books/thumbnails/`
- Audio: `/storage/app/public/books/audio/`

---

## 🔗 Book Relationships

Link related books using internal IDs:

- **Related (same)**: Same version/edition (`P001`)
- **Related (omnibus)**: Part of collection
- **Related (support)**: Teacher guides, workbooks (`P010|P011`)
- **Related (other language)**: Translations (`P001-a|P001-b`)

---

## 📖 Library References

### University of Hawaii
- **UH hard copy ref**: Reference number
- **UH hard copy call number**: `Pac.PL6252.K86K67 1979`
- **UH hard copy link**: Catalog URL
- **UH note**: Additional notes

### College of Micronesia
- **COM hard copy ref**: Reference number
- **COM hard copy call number**: Call number
- **COM hard copy ref NOTE**: Notes

---

## ✅ Access Level Mapping

| CSV Value | Database Value | Description |
|-----------|---------------|-------------|
| Y | full | Full access, PDF available |
| N | unavailable | No PDF available |
| L | limited | Limited/restricted access |

---

## 📏 Validation Rules

### String Lengths
- **ID**: Max 50 characters
- **PALM code**: Max 100 characters
- **Title/Subtitle**: Max 500 characters

### Integer Ranges
- **Year**: 1900 - 2100
- **Pages**: Minimum 1

### Unique Constraints
- **ID** (internal_id): Must be unique
- **PALM code**: Must be unique if provided

---

## 🔧 Common Format Patterns

### Internal ID Format
```
P###-a
```
- `P` = Prefix
- `###` = Number (001, 002, etc.)
- `-a` = Version letter

Examples: `P001-a`, `P026-b`, `P100-a`

### PALM Code Format
```
Alphanumeric
```
Examples: `TAW14`, `KNT1`, `PoOJ22`, `TCC4`

### Name Format
```
Last, First
```
Examples: `Smith, John`, `Doe, Jane`

Or:
```
Single name
```
Examples: `Tinngin`, `Wren`

---

## 📝 Text Fields

### Short Text (Descriptions)
- **Description**: `books.description` (long text)
- **TOC**: `books.toc` (table of contents)
- **Notes (issue)**: `books.notes_issue`
- **Notes (content)**: `books.notes_content`
- **CONTACT**: `books.contact` (ordering information)

### Line Breaks in CSV
Use actual line breaks within quoted cells:
```csv
"Chapter 1: Introduction
Chapter 2: Main Content
Chapter 3: Conclusion"
```

---

## 🚨 Common Errors & Solutions

### ❌ Error: Missing required field
**Solution**: Ensure every row has ID and Title

### ❌ Error: Duplicate ID
**Solution**: Check for repeated internal_id values

### ❌ Error: Invalid access level
**Solution**: Use only Y, N, or L for UPLOADED

### ❌ Error: File not found
**Solution**: Upload PDF/thumbnail files to server first

### ❌ Error: Special characters corrupted
**Solution**: Save file as UTF-8 encoding

### ❌ Error: Related book not found
**Solution**: Ensure related book IDs exist before import

---

## 📦 File Format Requirements

### Encoding
- **Character Set**: UTF-8
- **BOM**: Optional (UTF-8 BOM accepted)

### Delimiters
- **Field Separator**: Comma `,`
- **Text Qualifier**: Double quote `"`
- **Multi-Value Separator**: Pipe `|`

### Save As
- **Excel**: "CSV UTF-8 (Comma delimited) (*.csv)"
- **Google Sheets**: File → Download → CSV
- **LibreOffice**: Save As → Text CSV (.csv) → Character set: UTF-8

---

## 🎯 Import Modes

When importing via admin panel:

- **create_only**: Only create new books (skip existing)
- **update_only**: Only update existing books (skip new)
- **upsert**: Create new OR update existing (default)
- **create_duplicates**: Allow duplicates with new IDs

---

## 📥 Quick Import Checklist

Before importing:

- [ ] File saved as UTF-8
- [ ] All required fields filled (ID, Title)
- [ ] No duplicate IDs
- [ ] UPLOADED column uses Y/N/L
- [ ] Multi-value fields use pipe `|` separator
- [ ] PDF and thumbnail files uploaded to server
- [ ] Related book IDs exist in database
- [ ] Years are valid (1900-2100)

---

## 📚 Templates Available

### 1. Blank Template
**File**: `storage/csv-templates/book-import-template.csv`
- Empty template with headers
- Ready for data entry

### 2. Example Template
**File**: `storage/csv-templates/book-import-example.csv`
- 3 sample books
- Shows all field formats
- Use as reference

---

## 🔍 More Information

- **Complete Documentation**: `/docs/CSV_FIELD_MAPPING.md`
- **Implementation TODO**: `CSV_IMPORT_TODO.md`
- **Templates README**: `/storage/csv-templates/README.md`

---

**Version**: 1.0
**Last Updated**: 2025-11-06
**Maintained By**: Development Team
