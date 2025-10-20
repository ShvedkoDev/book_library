# CSV Field to Database Mapping - MTDL Project

**Date:** 2025-10-20
**Status:** Complete Mapping
**Source CSV:** categories explanations.csv (64 metadata fields)

---

## Complete Field Mapping Table

| # | CSV Field | Table | Field Name | Notes |
|---|-----------|-------|------------|-------|
| 1 | **ID** | `books` | `internal_id` | VARCHAR(50) UNIQUE |
| 2 | **Purpose** | `classification_values` → `book_classifications` | Linked via classification_type = 'Purpose' | Many-to-many pivot |
| 3 | **Genre** | `classification_values` → `book_classifications` | Linked via classification_type = 'Genre' | Many-to-many pivot |
| 4 | **Sub-genre** | `classification_values` | As child of Genre (parent_id) | Hierarchical |
| 5 | **Type** | `classification_values` → `book_classifications` | Linked via classification_type = 'Type' | Many-to-many pivot |
| 6 | **Themes/Uses** | `classification_values` → `book_classifications` | Linked via classification_type = 'Themes/Uses' | Many-to-many pivot |
| 7 | **Keywords** | `book_keywords` | `keyword` | Existing table, one row per keyword |
| 8 | **Collection** | `collections` → `books` | `collection_id` (FK) | Displayed above title |
| 9 | **Physical type** | `books` | `physical_type` | ENUM: book/journal/magazine/workbook/poster/other |
| 10 | **Title** | `books` | `title` | VARCHAR(500) NOT NULL |
| 11 | **Sub-title** | `books` | `subtitle` | VARCHAR(500) NULL |
| 12 | **Translated-title** | `books` | `translated_title` | VARCHAR(500) NULL |
| 13 | **PALM code** | `books` | `palm_code` | VARCHAR(100) UNIQUE |
| 14 | **Related (same)** | `book_relationships` | relationship_type = 'same_version' | With relationship_code |
| 15 | **Related (omnibus)** | `book_relationships` | relationship_type = 'same_language' | With relationship_code |
| 16 | **Related (support)** | `book_relationships` | relationship_type = 'supporting' | With relationship_code |
| 17 | **Related (same title, different language)** | `book_relationships` | relationship_type = 'other_language' | Matched by translated_title |
| 18 | **Year** | `books` | `publication_year` | INT NULL |
| 19 | **Author** | `creators` → `book_creators` | creator_type = 'author', sort_order = 0 | Many-to-many |
| 20 | **Author2** | `creators` → `book_creators` | creator_type = 'author', sort_order = 1 | Many-to-many |
| 21 | **Author3** | `creators` → `book_creators` | creator_type = 'author', sort_order = 2 | Many-to-many |
| 22 | **Other creator** | `creators` → `book_creators` | creator_type = 'contributor', sort_order = 0 | With role_description |
| 23 | **Other creator ROLE** | `book_creators` | `role_description` | Custom text for contributor role |
| 24 | **Other creator2** | `creators` → `book_creators` | creator_type = 'contributor', sort_order = 1 | With role_description |
| 25 | **Other creator2 ROLE** | `book_creators` | `role_description` | Custom text for contributor2 role |
| 26 | **Illustrator** | `creators` → `book_creators` | creator_type = 'illustrator', sort_order = 0 | Many-to-many |
| 27 | **Illustrator2** | `creators` → `book_creators` | creator_type = 'illustrator', sort_order = 1 | Many-to-many |
| 28 | **Illustrator3** | `creators` → `book_creators` | creator_type = 'illustrator', sort_order = 2 | Many-to-many |
| 29 | **Illustrator4** | `creators` → `book_creators` | creator_type = 'illustrator', sort_order = 3 | Many-to-many |
| 30 | **Illustrator5** | `creators` → `book_creators` | creator_type = 'illustrator', sort_order = 4 | Many-to-many |
| 31 | **Publisher** | `publishers` → `books` | `publisher_id` (FK) | Foreign key to publishers.name |
| 32 | **Contributor / Project / Partner** | `publishers` | `program_name` | VARCHAR(255) - Publisher sub-program |
| 33 | **Pages** | `books` | `pages` | INT NULL |
| 34 | **Language 1** | `languages` → `book_languages` | language_id, is_primary = TRUE | Many-to-many, uses name |
| 35 | **ISO (Language 1)** | `languages` | `code` | ISO language code for Language 1 |
| 36 | **Language 2** | `languages` → `book_languages` | language_id, is_primary = FALSE | Many-to-many, uses name |
| 37 | **ISO (Language 2)** | `languages` | `code` | ISO language code for Language 2 |
| 38 | **Island** | `geographic_locations` → `book_locations` | location_type = 'island' | Many-to-many pivot |
| 39 | **State** | `geographic_locations` → `book_locations` | location_type = 'state' | Many-to-many pivot |
| 40 | **Learner level** | `classification_values` → `book_classifications` | Linked via classification_type = 'Learner Level' | Many-to-many pivot |
| 41 | **VLA standard** | `books` | `vla_standard` | VARCHAR(255) NULL |
| 42 | **VLA benchmark** | `books` | `vla_benchmark` | VARCHAR(255) NULL |
| 43 | **TOC** | `books` | `toc` | TEXT NULL - Table of Contents |
| 44 | **Notes related to the issue** | `books` | `notes_issue` | TEXT NULL |
| 45 | **Notes related to content** | `books` | `notes_content` | TEXT NULL |
| 46 | **ABSTRACT/DESCRIPTION** | `books` | `description` | TEXT NULL |
| 47 | **UPLOADED** | `books` | `access_level` | ENUM: full/limited/unavailable |
| 48 | **DIGITAL SOURCE** | `book_files` | `digital_source` | For primary PDF - where it came from |
| 49 | **DOCUMENT FILENAME** | `book_files` | `filename`, `file_path` | file_type = 'pdf', is_primary = TRUE |
| 50 | **THUMBNAIL FILENAME** | `book_files` | `filename`, `file_path` | file_type = 'thumbnail', is_primary = TRUE |
| 51 | **Name match check** | _Not stored_ | Internal validation field | Not needed in DB |
| 52 | **ALTERNATIVE DOCUMENT FILENAME** | `book_files` | `filename`, `file_path` | file_type = 'pdf', is_primary = FALSE |
| 53 | **ALTERNATIVE THUMBNAIL FILENAME** | `book_files` | `filename`, `file_path` | file_type = 'thumbnail', is_primary = FALSE |
| 54 | **ALTERNATIVE DIGITAL SOURCE** | `book_files` | `digital_source` | For alternative PDF |
| 55 | **Coupled audio** | `book_files` | `filename`, `file_path` | file_type = 'audio' |
| 56 | **Coupled video** | `book_files` | `external_url` | file_type = 'video' (YouTube/Vimeo links) |
| 57 | **CONTACT** | `books` | `contact` | TEXT NULL - Hard copy ordering info |
| 58 | **UH hard copy ref** | `library_references` | `reference_number` | library_code = 'UH' |
| 59 | **UH hard copy link** | `library_references` | `catalog_link` | library_code = 'UH' |
| 60 | **UH hard copy call number** | `library_references` | `call_number` | library_code = 'UH' |
| 61 | **UH note** | `library_references` | `notes` | library_code = 'UH' |
| 62 | **COM hard copy ref** | `library_references` | `reference_number` | library_code = 'COM' |
| 63 | **COM hard copy call number** | `library_references` | `call_number` | library_code = 'COM' |
| 64 | **COM hard copy ref NOTE** | `library_references` | `notes` | library_code = 'COM' |

---

## Summary by Database Table

### 📚 `books` table (19 direct fields)
**Identity & Classification:**
- `internal_id` ← CSV: ID
- `palm_code` ← CSV: PALM code
- `physical_type` ← CSV: Physical type

**Basic Information:**
- `title` ← CSV: Title
- `subtitle` ← CSV: Sub-title
- `translated_title` ← CSV: Translated-title

**Publishing Details:**
- `collection_id` (FK) ← CSV: Collection
- `publisher_id` (FK) ← CSV: Publisher
- `publication_year` ← CSV: Year
- `pages` ← CSV: Pages

**Content & Description:**
- `description` ← CSV: ABSTRACT/DESCRIPTION
- `toc` ← CSV: TOC
- `notes_issue` ← CSV: Notes related to the issue
- `notes_content` ← CSV: Notes related to content
- `contact` ← CSV: CONTACT

**Educational Standards:**
- `vla_standard` ← CSV: VLA standard
- `vla_benchmark` ← CSV: VLA benchmark

**Access Control:**
- `access_level` ← CSV: UPLOADED

---

### 👥 `book_creators` pivot table (11 creator entries per book max)

**Authors (3 max):**
- `creator_type = 'author'`, `sort_order = 0` ← CSV: Author
- `creator_type = 'author'`, `sort_order = 1` ← CSV: Author2
- `creator_type = 'author'`, `sort_order = 2` ← CSV: Author3

**Illustrators (5 max):**
- `creator_type = 'illustrator'`, `sort_order = 0` ← CSV: Illustrator
- `creator_type = 'illustrator'`, `sort_order = 1` ← CSV: Illustrator2
- `creator_type = 'illustrator'`, `sort_order = 2` ← CSV: Illustrator3
- `creator_type = 'illustrator'`, `sort_order = 3` ← CSV: Illustrator4
- `creator_type = 'illustrator'`, `sort_order = 4` ← CSV: Illustrator5

**Contributors (2 max with custom roles):**
- `creator_type = 'contributor'`, `sort_order = 0`, `role_description` ← CSV: Other creator + Other creator ROLE
- `creator_type = 'contributor'`, `sort_order = 1`, `role_description` ← CSV: Other creator2 + Other creator2 ROLE

---

### 🌍 `book_languages` pivot table (2 languages max per book)

**Primary Language:**
- `language_id` (FK to languages.id), `is_primary = TRUE` ← CSV: Language 1 + ISO (Language 1)

**Secondary Language:**
- `language_id` (FK to languages.id), `is_primary = FALSE` ← CSV: Language 2 + ISO (Language 2)

---

### 🏷️ `book_classifications` pivot table (6 classification dimensions)

Links books to classification values via many-to-many:
- Classification Type: **Purpose** ← CSV: Purpose
- Classification Type: **Genre** ← CSV: Genre
- Classification Type: **Sub-genre** ← CSV: Sub-genre (child of Genre)
- Classification Type: **Type** ← CSV: Type
- Classification Type: **Themes/Uses** ← CSV: Themes/Uses
- Classification Type: **Learner Level** ← CSV: Learner level

---

### 📍 `book_locations` pivot table (Geographic filtering)

Links books to geographic locations:
- `location_type = 'island'` ← CSV: Island
- `location_type = 'state'` ← CSV: State

---

### 🔗 `book_relationships` table (4 relationship types)

**Relationship Types:**
- `relationship_type = 'same_version'`, `relationship_code` ← CSV: Related (same)
- `relationship_type = 'same_language'`, `relationship_code` ← CSV: Related (omnibus)
- `relationship_type = 'supporting'`, `relationship_code` ← CSV: Related (support)
- `relationship_type = 'other_language'` ← CSV: Related (same title, different language)

---

### 📁 `book_files` table (Multiple file versions)

**Primary Files:**
- `file_type = 'pdf'`, `is_primary = TRUE`, `filename`, `file_path`, `digital_source` ← CSV: DOCUMENT FILENAME + DIGITAL SOURCE
- `file_type = 'thumbnail'`, `is_primary = TRUE`, `filename`, `file_path` ← CSV: THUMBNAIL FILENAME

**Alternative Files:**
- `file_type = 'pdf'`, `is_primary = FALSE`, `filename`, `file_path`, `digital_source` ← CSV: ALTERNATIVE DOCUMENT FILENAME + ALTERNATIVE DIGITAL SOURCE
- `file_type = 'thumbnail'`, `is_primary = FALSE`, `filename`, `file_path` ← CSV: ALTERNATIVE THUMBNAIL FILENAME

**Coupled Media:**
- `file_type = 'audio'`, `filename`, `file_path` ← CSV: Coupled audio
- `file_type = 'video'`, `external_url` ← CSV: Coupled video

---

### 🏛️ `library_references` table (Physical library copies)

**University of Hawaii (UH):**
- `library_code = 'UH'`, `reference_number` ← CSV: UH hard copy ref
- `library_code = 'UH'`, `catalog_link` ← CSV: UH hard copy link
- `library_code = 'UH'`, `call_number` ← CSV: UH hard copy call number
- `library_code = 'UH'`, `notes` ← CSV: UH note

**COM Library:**
- `library_code = 'COM'`, `reference_number` ← CSV: COM hard copy ref
- `library_code = 'COM'`, `call_number` ← CSV: COM hard copy call number
- `library_code = 'COM'`, `notes` ← CSV: COM hard copy ref NOTE

---

### 📖 `publishers` table

**Publisher Information:**
- `name` ← CSV: Publisher
- `program_name` ← CSV: Contributor / Project / Partner

---

### 🔤 `book_keywords` table (Existing)

**Keywords:**
- `keyword` ← CSV: Keywords (one row per keyword)

---

## Field Coverage Summary

| Category | CSV Fields | Database Implementation | Status |
|----------|-----------|------------------------|--------|
| **Book Identity** | 9 fields | `books` table direct columns | ✅ Complete |
| **Creators** | 11 fields | `book_creators` pivot with sort_order | ✅ Complete |
| **Languages** | 4 fields | `book_languages` pivot with is_primary | ✅ Complete |
| **Classifications** | 6 fields | `book_classifications` via taxonomy system | ✅ Complete |
| **Geographic** | 2 fields | `book_locations` pivot | ✅ Complete |
| **Relationships** | 4 fields | `book_relationships` with relationship_type | ✅ Complete |
| **Files** | 9 fields | `book_files` with file_type and is_primary | ✅ Complete |
| **Library References** | 7 fields | `library_references` with library_code | ✅ Complete |
| **Publishing** | 2 fields | `publishers` table | ✅ Complete |
| **Content** | 9 fields | `books` table direct columns | ✅ Complete |
| **Validation** | 1 field | Not stored (internal use only) | ✅ N/A |

**Total CSV Fields:** 64
**Mapped to Database:** 63 (1 internal validation field excluded)
**Coverage:** 100% ✅

---

## Import Process Flow

When importing a CSV row, the process follows this sequence:

1. **Parse CSV Row** → Extract all 64 fields
2. **Create/Find Publishers** → Match or create publisher, set program_name
3. **Create/Find Collections** → Match or create collection
4. **Create/Find Languages** → Match by ISO code (Language 1 & 2)
5. **Create/Find Creators** → Match or create all authors, illustrators, contributors
6. **Create/Find Classifications** → Match or create Purpose, Genre, Type, etc.
7. **Create/Find Locations** → Match or create Islands and States
8. **Create Book Record** → Insert into `books` table with all direct fields
9. **Link Languages** → Insert into `book_languages` (is_primary flag)
10. **Link Creators** → Insert into `book_creators` (with sort_order and role_description)
11. **Link Classifications** → Insert into `book_classifications`
12. **Link Locations** → Insert into `book_locations`
13. **Process Files** → Insert into `book_files` (primary + alternative PDFs, thumbnails, audio, video)
14. **Create Relationships** → Insert into `book_relationships` based on relationship codes
15. **Add Library References** → Insert into `library_references` (UH and COM)
16. **Extract Keywords** → Parse and insert into `book_keywords`

---

## Notes

- **Hierarchical Classifications:** Sub-genre uses `parent_id` to link to Genre values
- **Custom Roles:** Other creator roles are stored as free text in `role_description`
- **Bilingual Books:** Language 2 is optional, only stored if present in CSV
- **Multiple Files:** Alternative PDFs and thumbnails are marked with `is_primary = FALSE`
- **External Media:** Video files use `external_url` instead of file storage
- **Library Systems:** Extensible to add more library codes beyond UH and COM
- **Relationship Codes:** Used to group related books (same, omnibus, support types)
- **Name Match Check:** Internal CSV validation field, not persisted to database

---

**Last Updated:** 2025-10-20
**Reference:** DATABASE_REDESIGN_PROPOSAL.md
