# CSV Import Quick Reference - Pipe Separator Guide

## TL;DR

Use the **pipe character `|`** to separate multiple values in a single CSV cell.

```
AuthorField: "Smith, John|Jones, Mary|Brown, Alice"
LanguageField: "English|Spanish|French"
KeywordField: "education|culture|reading|traditions"
```

## Multi-Value Fields

### Authors & Creators ✓ Can use pipes
- Author
- Author2
- Author3
- Illustrator through Illustrator5
- Other creator, Other creator2

**Example**: `Smith, John|Jones, Mary|Davis, Sarah`

### Languages ✓ Can use pipes
- Language 1 (Primary)
- Language 2 (Secondary)

**Example**: `English|Spanish|Yapese|Chuukese`

### Classifications ✓ Can use pipes
- Purpose
- Genre
- Sub-genre
- Type
- Themes/Uses
- Learner level

**Example**: `Fiction|Adventure|Fantasy`

### Locations ✓ Can use pipes
- Island
- State

**Example**: `Chuuk|Pohnpei|Kosrae|Yap`

### Keywords ✓ Can use pipes
- Keywords

**Example**: `education|reading|culture|traditions`

### Media ✓ Can use pipes
- Coupled audio
- Coupled video

**Example**: `chapter1.mp3|chapter2.mp3|chapter3.mp3`

### Book Relationships ✓ Can use pipes
- Related (same)
- Related (omnibus)
- Related (support)
- Related (translated)

**Example**: `YSCI001|YSCI002|YSCI003`

## Single-Value Fields ✗ NO pipes
- Title
- Sub-title
- Translated-title
- ID
- PALM code
- Physical type
- Year
- Pages
- Collection
- Publisher
- TOC
- DESCRIPTION
- ABSTRACT
- And most others (single values only)

## Format Rules

✓ **Do This:**
- `Smith, John|Jones, Mary|Brown, Alice` - Author names with pipe separator
- `English|Spanish|French` - Multiple languages
- `Fiction|Adventure` - Multiple genres
- `value1|value2|value3` - Any pipe-separated values

✗ **Don't Do This:**
- `Smith, John; Jones, Mary` - Using semicolon (wrong separator)
- `Smith, John, Jones, Mary` - Using comma between authors (ambiguous)
- `English, Spanish, French` - Using comma between languages
- Spaces around pipe are OK but not necessary (auto-trimmed)

## Common Examples

### Example 1: Multiple Authors
```csv
ID,Title,Author
P001,"Adventure Story","Smith, John|Jones, Mary|Brown, Alice"
```
**Result**: 3 authors linked to the book

### Example 2: Bilingual Book
```csv
ID,Title,Language 1,Language 2
B001,"English-Spanish Book","English","Spanish"
```
**Result**: English as primary, Spanish as secondary

### Example 3: Multilingual Book
```csv
ID,Title,Language 1,Language 2
M001,"Four Language Book","English|Spanish|French","German|Italian|Portuguese"
```
**Result**: 3 primary languages + 3 secondary languages (total 6)

### Example 4: Classified Book
```csv
ID,Title,Genre,Sub-genre,Learner level
C001,"Adventure Book","Fiction|Fantasy","Science Fiction|Adventure","Middle School|High School"
```
**Result**: Multiple genre, sub-genre, and learner level classifications

### Example 5: Geographic Content
```csv
ID,Title,Island,Keywords
G001,"Island Stories","Chuuk|Pohnpei|Kosrae","culture|traditions|history"
```
**Result**: Content about 3 islands with 3 keywords

## Admin Interface (/admin/csv-import)

1. **Upload CSV file** - Select your file
2. **Choose mode** - upsert, create_only, update_only, or create_duplicates
3. **Options** - Enable "Create missing relations" (recommended)
4. **Validate** - Click "Validate only" to check for errors
5. **Import** - Click "Import CSV" to start the import
6. **Review** - Check results and error reports

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Authors not created | Use pipe separator: `Author1\|Author2` |
| Only first value imported | Check you're using pipe `\|` not comma or semicolon |
| Import fails on validation | Check CSV format, use UTF-8 encoding |
| Languages not linking | Verify language names (English, not english) |
| Spaces in values | Don't worry - automatically trimmed |

## In Real Usage

**Your CSV might look like:**
```
ID,PALM code,Title,Author,Language 1,Island,Genre,Keywords,Illustrator
P001,TAW001,"My Story","Smith, John|Jones, Mary","English|Chuukese","Chuuk|Pohnpei","Fiction|Adventure","education|culture","Davis, Sarah"
P002,TAW002,"Learning Book","Brown, Alice","English","Kosrae","Educational|Reference","learning|traditions","Green, Tom|Wilson, Jane"
```

**What gets created:**
- Book P001 with:
  - 2 authors
  - 2 languages
  - 2 islands
  - 2 genres
  - 2 keywords
  - 1 illustrator
- Book P002 with:
  - 1 author
  - 1 language
  - 1 island
  - 2 genres
  - 2 keywords
  - 2 illustrators

## Getting More Help

- **Full Documentation**: `/docs/CSV_FIELD_MAPPING.md`
- **Admin Import Page**: `/admin/csv-import`
- **Implementation Details**: `PIPE_SEPARATOR_IMPLEMENTATION.md`

---

**Remember**: Use pipe `|` to separate multiple values in supporting fields. That's it!

