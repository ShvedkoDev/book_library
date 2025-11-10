# CSV Import Templates

This directory contains CSV templates for importing books into the Micronesian Teachers Digital Library.

## Files in This Directory

### 1. book-import-template.csv
**Purpose**: Blank template with headers only

**Use this when**:
- Starting a new book collection from scratch
- Need to see all available columns
- Want a clean slate for data entry

**Structure**:
- Row 1: Human-readable column headers
- Row 2: Database field mapping (technical reference)
- Row 3+: Empty (ready for your data)

---

### 2. book-import-example.csv
**Purpose**: Template with 3 sample books demonstrating all field types

**Use this when**:
- Learning the CSV format
- Understanding multi-value fields (pipe-separated)
- Need examples of proper data formatting

**Included Examples**:
1. **EXAMPLE-001**: Basic literacy book with multiple authors and illustrator
2. **EXAMPLE-002**: Science textbook with alternative files and library references
3. **EXAMPLE-003**: Mathematics workbook with audio files

---

## Quick Start Guide

### For First-Time Import (1000+ books):

1. **Start with the template**:
   ```bash
   cp book-import-template.csv my-books.csv
   ```

2. **Open in spreadsheet software**:
   - Excel, Google Sheets, LibreOffice Calc
   - Ensure UTF-8 encoding is maintained

3. **Fill in your data**:
   - Required: ID, Title
   - Recommended: Language 1, Collection, Publisher, Year, UPLOADED
   - See example file for formatting

4. **Save as CSV**:
   - UTF-8 encoding
   - Comma delimited
   - Keep header rows intact

5. **Import via admin panel**:
   - Navigate to `/admin/csv-import` (when implemented)
   - Upload your CSV file
   - Run validation first
   - Review errors and fix
   - Execute import

---

## Field Format Examples

### Multi-Value Fields (use pipe `|` separator):

**Multiple Authors**:
```
"Smith, John","Doe, Jane","Wilson, Bob"
```
Or in single column:
```
Smith, John|Doe, Jane|Wilson, Bob
```

**Multiple Classifications**:
```
Grade 1|Grade 2|Grade 3
```

**Multiple Keywords**:
```
education|literacy|reading|pacific islands
```

**Related Books**:
```
P001|P002|P003
```

### Special Characters:

**Glottal stops and diacritics** (use UTF-8):
```
A?a?n a?tin mwa?a?n we pikinik
```

**Quotes in text** (double the quotes):
```
"The author said ""Hello"" to students"
```

### Access Level:

```
Y = Full access (PDF available)
N = Unavailable (no PDF)
L = Limited access (restricted)
```

### Years with Uncertainty:

```
1978?  → Will be imported as 1978 (question mark stripped)
1978   → Standard format
```

---

## Column Descriptions

### Core Required Fields:
- **ID**: Unique identifier (e.g., `P001-a`)
- **Title**: Book title

### Highly Recommended:
- **Language 1**: Primary language (e.g., `Chuukese`)
- **Collection**: Collection name (e.g., `PALM trial`)
- **Publisher**: Publisher name
- **Year**: Publication year
- **UPLOADED**: Access level (`Y`, `N`, or `L`)

### Multi-Value Relationship Fields:
- **Purpose, Genre, Sub-genre, Type, Themes/Uses**: Classifications
- **Keywords**: Pipe-separated keywords
- **Author, Author2, Author3**: Multiple authors
- **Illustrator** (1-5): Multiple illustrators
- **Island, State**: Geographic locations

### Creator Fields with Roles:
- **Other creator + Other creator ROLE**: For translators, editors, etc.
  - Example: Creator: `Johnson, Mary` | Role: `translated by`

### File References:
- **DOCUMENT FILENAME**: PDF filename
- **THUMBNAIL FILENAME**: Thumbnail image filename
- **Coupled audio**: Audio file(s) - pipe-separated
- **Coupled video**: Video URL(s) - pipe-separated

### Library References:
- **UH hard copy ref/call number/link**: University of Hawaii library
- **COM hard copy ref/call number**: College of Micronesia library

---

## Common Mistakes to Avoid

❌ **Wrong**: Using semicolons instead of pipes
```
Grade 1;Grade 2;Grade 3
```

✅ **Correct**: Use pipe separator
```
Grade 1|Grade 2|Grade 3
```

---

❌ **Wrong**: Inconsistent encoding (non-UTF-8)
```
A?a?n (displays as garbled characters)
```

✅ **Correct**: Save file as UTF-8
```
A?a?n (displays correctly)
```

---

❌ **Wrong**: Leaving required fields empty
```
ID: (blank)
Title: (blank)
```

✅ **Correct**: Fill required fields
```
ID: P001-a
Title: Example Book
```

---

❌ **Wrong**: Using wrong access level values
```
UPLOADED: Yes
UPLOADED: Available
```

✅ **Correct**: Use standard codes
```
UPLOADED: Y
UPLOADED: N
UPLOADED: L
```

---

## Data Validation Checklist

Before importing, verify:

- [ ] **Encoding**: File saved as UTF-8
- [ ] **Headers**: Both header rows present and unmodified
- [ ] **Required Fields**: All books have ID and Title
- [ ] **Unique IDs**: No duplicate internal IDs
- [ ] **Access Levels**: UPLOADED column uses Y/N/L
- [ ] **Years**: Valid years between 1900-2100
- [ ] **Files Exist**: All referenced PDF/thumbnail files uploaded to server
- [ ] **Multi-Values**: Pipe separator used (not comma, semicolon, etc.)
- [ ] **Relationships**: Related book IDs exist in the database
- [ ] **Languages**: Language names match database (or will be created)

---

## Need Help?

### Documentation:
- **Full Field Reference**: See `/docs/CSV_FIELD_MAPPING.md`
- **Import Process**: See `CSV_IMPORT_TODO.md`

### Common Issues:

**Issue**: Import fails with "Missing required field"
**Solution**: Ensure every row has at minimum ID and Title

**Issue**: Special characters display incorrectly
**Solution**: Save file as UTF-8 with BOM (Excel: CSV UTF-8)

**Issue**: Relationships not created
**Solution**: Ensure related book IDs already exist in database

**Issue**: Files not found
**Solution**: Upload PDF/thumbnail files to server first:
- PDFs: `/storage/app/public/books/pdfs/`
- Thumbnails: `/storage/app/public/books/thumbnails/`

---

## Version History

- **v1.0** (2025-11-06): Initial template based on test.csv structure
- **Future**: Will be updated as import system evolves

---

**Maintained By**: Development Team
**Last Updated**: 2025-11-06
**Related Documentation**: `/docs/CSV_FIELD_MAPPING.md`
