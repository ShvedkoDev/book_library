# Database Redesign Proposal - MTDL Project

**Date:** 2025-10-20
**Status:** Proposal for Review
**Objective:** Optimize database structure to match CSV metadata requirements and enable efficient search/filtering

---

## 📋 Executive Summary

After analyzing the `categories explanations.csv` file (64 metadata fields) and reviewing the current database structure, I've identified significant gaps between what the CSV requires and what the current schema supports. This proposal outlines a normalized, search-optimized database design that:

- ✅ Captures all 64 metadata fields from the CSV
- ✅ Enables efficient filtering with AND logic across multiple dimensions
- ✅ Supports multilingual books (2 languages per book)
- ✅ Handles complex creator relationships (authors, illustrators, editors with custom roles)
- ✅ Manages multiple file versions (primary + alternative PDFs, thumbnails, audio, video)
- ✅ Tracks 4 types of book relationships (same version, same language, supporting materials, different language)
- ✅ Maintains search performance with proper indexing

---

## 🔍 CSV Analysis: 64 Metadata Fields

### Book Identity & Classification
1. **ID** - Internal unique ID
2. **PALM code** - External identification system
3. **Purpose** - Classification for filtering
4. **Genre** - Classification for filtering
5. **Sub-genre** - Classification for filtering
6. **Type** - Classification for filtering
7. **Themes/Uses** - Classification for filtering
8. **Keywords** - For search (all fields are searchable)
9. **Physical type** - Book/Journal/Magazine/etc.

### Book Information
10. **Collection** - Displayed above title, used for "More from collection"
11. **Title** - Main title
12. **Sub-title** - Second line
13. **Translated-title** - Popup or below subtitle
14. **Year** - Publication year
15. **Pages** - Page count
16. **ABSTRACT/DESCRIPTION** - Main descriptive paragraph
17. **TOC** - Table of Contents
18. **Notes related to the issue** - Publication notes
19. **Notes related to content** - Content notes

### Creators (Complex Structure)
20. **Author** - Primary author
21. **Author2** - Second author
22. **Author3** - Third author
23. **Other creator** - Editor/etc. name
24. **Other creator ROLE** - Custom role description
25. **Other creator2** - Second editor/etc.
26. **Other creator2 ROLE** - Custom role description
27. **Illustrator** - First illustrator
28. **Illustrator2** - Second illustrator
29. **Illustrator3** - Third illustrator
30. **Illustrator4** - Fourth illustrator
31. **Illustrator5** - Fifth illustrator

### Publishing Information
32. **Publisher** - Publisher name
33. **Contributor / Project / Partner** - Publisher program/series/department

### Language (Bilingual Support)
34. **Language 1** - Primary language name
35. **ISO (Language 1)** - Primary language ISO code
36. **Language 2** - Secondary language (if applicable)
37. **ISO (Language 2)** - Secondary language ISO code

### Geographic Classification
38. **Island** - Geographic filtering
39. **State** - Geographic filtering

### Educational Classification
40. **Learner level** - Classification for filtering
41. **VLA standard** - Internal educational standard
42. **VLA benchmark** - Internal educational benchmark

### Book Relationships (4 Types)
43. **Related (same)** - Other versions (same code grouping)
44. **Related (omnibus)** - Related titles same language (same code)
45. **Related (support)** - Supporting materials (same code)
46. **Related (same title, different language)** - Matched by identical translated title

### File Management (Multiple Versions)
47. **UPLOADED** - Access level: full/limited/unavailable
48. **DIGITAL SOURCE** - Where PDF came from
49. **DOCUMENT FILENAME** - Primary PDF filename
50. **THUMBNAIL FILENAME** - Primary thumbnail filename
51. **Name match check** - Internal validation
52. **ALTERNATIVE DOCUMENT FILENAME** - Secondary PDF scan
53. **ALTERNATIVE THUMBNAIL FILENAME** - Secondary thumbnail
54. **ALTERNATIVE DIGITAL SOURCE** - Source of alternative scan
55. **Coupled audio** - Related audio files
56. **Coupled video** - Related video files (can link to external platforms)

### Contact & Availability
57. **CONTACT** - Who to contact for hard copy orders

### Library References (Physical Copies)
58. **UH hard copy ref** - University of Hawaii reference
59. **UH hard copy link** - UH catalog link
60. **UH hard copy call number** - UH call number
61. **UH note** - UH-specific notes
62. **COM hard copy ref** - COM library reference
63. **COM hard copy call number** - COM call number
64. **COM hard copy ref NOTE** - COM-specific notes

---

## 🚨 Critical Gaps in Current Schema

### Gap 1: Multilingual Books Not Supported
**Current:** Single `language_id` foreign key in `books` table
**CSV Requirement:** Language 1 + Language 2 with separate ISO codes
**Impact:** Cannot store bilingual books correctly
**Solution:** Create `book_languages` pivot table with `is_primary` flag

### Gap 2: Creator Complexity Not Captured
**Current:** `book_authors` with enum role (author/co-author/editor/translator)
**CSV Requirement:**
- Authors (1-3)
- Other creators (2) with custom role text
- Illustrators (1-5)

**Impact:** Cannot store illustrators or custom creator roles
**Solution:** New `creators` + `book_creators` tables with `role_description` text field

### Gap 3: Missing Classification Taxonomy
**Current:** Generic `categories` table with parent_id hierarchy
**CSV Requirement:** Separate dimensions: Purpose, Genre, Sub-genre, Type, Themes/Uses, Learner Level, VLA Standard/Benchmark
**Impact:** Cannot filter by multiple classification dimensions properly
**Solution:** New `classification_types`, `classification_values`, `book_classifications` tables

### Gap 4: No Geographic Metadata
**Current:** No geographic fields
**CSV Requirement:** Island and State fields for filtering
**Impact:** Cannot filter books by geographic region
**Solution:** `geographic_locations` + `book_locations` tables

### Gap 5: Book Relationships Incomplete
**Current:** `book_editions` table with single `edition_type` enum
**CSV Requirement:** 4 distinct relationship types with grouping codes:
- Same (other versions)
- Omnibus (related same language)
- Support (supporting materials)
- Different language (matched by translated title)

**Impact:** Cannot represent all relationship types
**Solution:** `book_relationships` table with `relationship_type` + `relationship_code`

### Gap 6: Multiple File Versions Not Supported
**Current:** Single `pdf_file` and `cover_image` in `books` table
**CSV Requirement:**
- Primary PDF + thumbnail
- Alternative PDF + thumbnail (each with separate source)
- Coupled audio files
- Coupled video files (external links)

**Impact:** Cannot store alternative scans or media files
**Solution:** `book_files` table with `file_type`, `is_primary`, and `digital_source` per file

### Gap 7: Missing Book Metadata Fields
**Current books table missing:**
- `translated_title` - For multilingual display
- `palm_code` - External identifier
- `physical_type` - Book/Journal/Magazine
- `toc` - Table of Contents
- `notes_issue` - Publication notes
- `notes_content` - Content notes
- `digital_source` - PDF provenance (currently in book_files instead)
- `contact` - Hard copy ordering info

**Solution:** Add these columns to `books` table

### Gap 8: No Library Physical Copy References
**Current:** No library reference tracking
**CSV Requirement:** UH and COM library references with call numbers, links, notes
**Impact:** Cannot show "Find this book in a library" feature
**Solution:** `library_references` table

### Gap 9: Publisher Programs Not Tracked
**Current:** Basic publisher info
**CSV Requirement:** "Contributor / Project / Partner" (publisher sub-programs)
**Impact:** Cannot track publisher departments/series
**Solution:** Add `program_name` to `publishers` table

---

## 🎯 Recommended Database Schema

### 📚 CORE ENTITIES (Modified)

#### 1. `books` (ENHANCED)
```sql
CREATE TABLE books (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    internal_id VARCHAR(50) UNIQUE NULL,              -- CSV: ID
    palm_code VARCHAR(100) UNIQUE NULL,               -- CSV: PALM code
    title VARCHAR(500) NOT NULL,                      -- CSV: Title
    subtitle VARCHAR(500) NULL,                       -- CSV: Sub-title
    translated_title VARCHAR(500) NULL,               -- CSV: Translated-title
    physical_type ENUM('book', 'journal', 'magazine', 'workbook', 'poster', 'other') NULL, -- CSV: Physical type

    collection_id BIGINT UNSIGNED NULL,               -- FK to collections
    publisher_id BIGINT UNSIGNED NULL,                -- FK to publishers

    publication_year INT NULL,                        -- CSV: Year
    pages INT NULL,                                   -- CSV: Pages

    description TEXT NULL,                            -- CSV: ABSTRACT/DESCRIPTION
    toc TEXT NULL,                                    -- CSV: TOC
    notes_issue TEXT NULL,                            -- CSV: Notes related to the issue
    notes_content TEXT NULL,                          -- CSV: Notes related to content

    contact TEXT NULL,                                -- CSV: CONTACT

    access_level ENUM('full', 'limited', 'unavailable') DEFAULT 'unavailable', -- CSV: UPLOADED
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,

    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    sort_order INT DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_title (title),
    INDEX idx_search (access_level, is_active, publication_year),
    INDEX idx_identifiers (internal_id, palm_code),
    FULLTEXT idx_fulltext (title, subtitle, translated_title, description),

    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Changes:**
- Added `internal_id`, `palm_code`, `translated_title`, `physical_type`
- Added `toc`, `notes_issue`, `notes_content`, `contact`
- Removed single `language_id` (moved to `book_languages`)
- Removed `pdf_file`, `cover_image`, `file_size` (moved to `book_files`)
- Enhanced fulltext index to include all searchable fields

---

#### 2. `creators` (NEW - replaces `authors`)
```sql
CREATE TABLE creators (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    biography TEXT NULL,
    birth_year INT NULL,
    death_year INT NULL,
    nationality VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_name (name),
    FULLTEXT idx_fulltext (name, biography)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Rationale:** Unified table for authors, illustrators, editors, translators, etc.

---

#### 3. `book_creators` (NEW - replaces `book_authors`)
```sql
CREATE TABLE book_creators (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    creator_type ENUM('author', 'illustrator', 'editor', 'translator', 'contributor') NOT NULL,
    role_description VARCHAR(100) NULL,              -- For custom roles like "Other creator ROLE"
    sort_order INT DEFAULT 0,                        -- Maintains sequence (Author2, Author3, Illustrator1-5)
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_creator (book_id, creator_id, creator_type, role_description),
    INDEX idx_book (book_id),
    INDEX idx_creator (creator_id),
    INDEX idx_type (creator_type),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES creators(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Mapping to CSV:**
- Author, Author2, Author3 → `creator_type = 'author'`, `sort_order = 0, 1, 2`
- Illustrator, Illustrator2-5 → `creator_type = 'illustrator'`, `sort_order = 0-4`
- Other creator + ROLE → `creator_type = 'contributor'`, `role_description = custom text`

---

#### 4. `book_languages` (NEW - many-to-many)
```sql
CREATE TABLE book_languages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    language_id BIGINT UNSIGNED NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,                -- Distinguishes Language 1 vs Language 2
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_book_language (book_id, language_id),
    INDEX idx_book (book_id),
    INDEX idx_language_primary (language_id, is_primary),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**CSV Mapping:**
- Language 1 + ISO → `is_primary = TRUE`
- Language 2 + ISO → `is_primary = FALSE`

---

### 🏷️ CLASSIFICATION & TAXONOMY

#### 5. `classification_types` (NEW)
```sql
CREATE TABLE classification_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,                      -- e.g., "Purpose", "Genre", "Type", "Learner Level"
    slug VARCHAR(100) UNIQUE NOT NULL,               -- e.g., "purpose", "genre", "type"
    description TEXT NULL,
    allow_multiple BOOLEAN DEFAULT TRUE,             -- Can book have multiple values?
    use_for_filtering BOOLEAN DEFAULT TRUE,          -- Show in filter UI?
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Initial Data:**
- Purpose
- Genre
- Sub-genre (child of Genre)
- Type
- Themes/Uses
- Learner Level
- VLA Standard
- VLA Benchmark

---

#### 6. `classification_values` (NEW)
```sql
CREATE TABLE classification_values (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    classification_type_id BIGINT UNSIGNED NOT NULL,
    value VARCHAR(100) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,                  -- For hierarchical values (Genre > Sub-genre)
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_value (classification_type_id, value, parent_id),
    INDEX idx_type (classification_type_id, is_active),
    INDEX idx_parent (parent_id),

    FOREIGN KEY (classification_type_id) REFERENCES classification_types(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES classification_values(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Examples:**
- Type: Fiction, Non-Fiction, Poetry, Drama
- Genre: Science, History, Language Arts (with Sub-genre children)
- Learner Level: Preschool, Elementary, Secondary, Adult

---

#### 7. `book_classifications` (NEW - pivot)
```sql
CREATE TABLE book_classifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    classification_value_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_classification (book_id, classification_value_id),
    INDEX idx_book (book_id),
    INDEX idx_value (classification_value_id),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (classification_value_id) REFERENCES classification_values(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 🌍 GEOGRAPHIC METADATA

#### 8. `geographic_locations` (NEW)
```sql
CREATE TABLE geographic_locations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    location_type ENUM('island', 'state', 'region') NOT NULL,
    name VARCHAR(100) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,                  -- State > Island hierarchy
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_location (location_type, name, parent_id),
    INDEX idx_type (location_type, is_active),
    INDEX idx_parent (parent_id),

    FOREIGN KEY (parent_id) REFERENCES geographic_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**CSV Mapping:**
- Island field
- State field

---

#### 9. `book_locations` (NEW - pivot)
```sql
CREATE TABLE book_locations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_location (book_id, location_id),
    INDEX idx_book (book_id),
    INDEX idx_location (location_id),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES geographic_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 📖 RELATIONSHIPS & EDITIONS

#### 10. `book_relationships` (NEW - replaces `book_editions`)
```sql
CREATE TABLE book_relationships (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    related_book_id BIGINT UNSIGNED NOT NULL,
    relationship_type ENUM('same_version', 'same_language', 'supporting', 'other_language', 'custom') NOT NULL,
    relationship_code VARCHAR(50) NULL,              -- For grouping related books (CSV uses "same code")
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_relationship (book_id, related_book_id, relationship_type),
    INDEX idx_book_type (book_id, relationship_type),
    INDEX idx_code (relationship_code),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (related_book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**CSV Mapping:**
- Related (same) → `relationship_type = 'same_version'`, uses `relationship_code`
- Related (omnibus) → `relationship_type = 'same_language'`, uses `relationship_code`
- Related (support) → `relationship_type = 'supporting'`, uses `relationship_code`
- Related (different language) → `relationship_type = 'other_language'`, matched by `translated_title`

---

### 📁 FILE MANAGEMENT

#### 11. `book_files` (NEW)
```sql
CREATE TABLE book_files (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    file_type ENUM('pdf', 'thumbnail', 'audio', 'video') NOT NULL,
    file_path VARCHAR(500) NOT NULL,                 -- Full storage path
    filename VARCHAR(255) NOT NULL,                  -- Original filename
    file_size BIGINT NULL,                           -- In bytes
    mime_type VARCHAR(100) NULL,
    is_primary BOOLEAN DEFAULT FALSE,                -- Primary vs alternative
    digital_source VARCHAR(500) NULL,                -- CSV: DIGITAL SOURCE / ALTERNATIVE DIGITAL SOURCE
    external_url VARCHAR(500) NULL,                  -- For videos (YouTube/Vimeo links)
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_book_type (book_id, file_type, is_primary),
    INDEX idx_active (is_active),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**CSV Mapping:**
- DOCUMENT FILENAME → `file_type = 'pdf'`, `is_primary = TRUE`
- THUMBNAIL FILENAME → `file_type = 'thumbnail'`, `is_primary = TRUE`
- ALTERNATIVE DOCUMENT → `file_type = 'pdf'`, `is_primary = FALSE`
- ALTERNATIVE THUMBNAIL → `file_type = 'thumbnail'`, `is_primary = FALSE`
- Coupled audio → `file_type = 'audio'`
- Coupled video → `file_type = 'video'`, uses `external_url`

---

### 🏛️ LIBRARY REFERENCES

#### 12. `library_references` (NEW)
```sql
CREATE TABLE library_references (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    library_code VARCHAR(50) NOT NULL,               -- e.g., "UH", "COM"
    library_name VARCHAR(255) NOT NULL,              -- Full library name
    reference_number VARCHAR(100) NULL,              -- CSV: UH/COM hard copy ref
    call_number VARCHAR(100) NULL,                   -- CSV: UH/COM hard copy call number
    catalog_link VARCHAR(500) NULL,                  -- CSV: UH hard copy link
    notes TEXT NULL,                                 -- CSV: UH/COM note
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_book (book_id),
    INDEX idx_library (library_code),

    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**CSV Mapping:**
- UH hard copy ref, link, call number, note → `library_code = 'UH'`
- COM hard copy ref, call number, note → `library_code = 'COM'`

---

### 📚 PUBLISHERS & COLLECTIONS

#### 13. `publishers` (ENHANCED)
```sql
CREATE TABLE publishers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    program_name VARCHAR(255) NULL,                  -- CSV: Contributor / Project / Partner
    address TEXT NULL,
    website VARCHAR(255) NULL,
    contact_email VARCHAR(255) NULL,
    established_year INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Change:** Added `program_name` for publisher departments/series/projects

---

#### 14. `collections` (KEEP AS IS)
```sql
-- Current structure is good
-- Used for CSV: Collection field
-- Displayed above book title
-- Used for "More books from the same collection" feature
```

---

#### 15. `languages` (KEEP AS IS)
```sql
-- Current structure is good
-- Stores ISO codes and language names
```

---

### 🔍 KEYWORDS & IDENTIFIERS (KEEP)

#### 16. `book_keywords` (KEEP)
```sql
-- Current implementation is optimal
-- Separate rows per keyword enable efficient indexing
```

#### 17. `book_identifiers` (KEEP)
```sql
-- Current implementation is good
-- Consider adding 'PALM' to identifier_type enum if needed
```

---

### 👥 USER ENGAGEMENT (KEEP)

Keep these existing tables - they're well designed:
- `book_ratings`
- `book_reviews`
- `book_bookmarks`
- `book_downloads`
- `book_views`
- `users` (with modifications)
- `terms_of_use_versions`
- `user_terms_acceptance`

---

## 🎯 Search Query Optimization Strategy

### Full-Text Search Indexes
```sql
-- Books
ALTER TABLE books ADD FULLTEXT idx_ft_books (title, subtitle, translated_title, description);

-- Creators
ALTER TABLE creators ADD FULLTEXT idx_ft_creators (name, biography);

-- Keywords (already indexed individually)
```

### Composite Indexes for Common Filter Queries
```sql
-- Multi-dimensional filtering
CREATE INDEX idx_book_filters ON books (access_level, is_active, publication_year);

-- Creator type queries
CREATE INDEX idx_creator_type_order ON book_creators (book_id, creator_type, sort_order);

-- Classification filtering (for AND logic)
CREATE INDEX idx_class_value_book ON book_classifications (classification_value_id, book_id);

-- Geographic filtering
CREATE INDEX idx_location_book ON book_locations (location_id, book_id);

-- Language filtering
CREATE INDEX idx_lang_book_primary ON book_languages (language_id, book_id, is_primary);

-- File retrieval
CREATE INDEX idx_file_type_primary ON book_files (book_id, file_type, is_primary);
```

### Example Search Query (Multi-Filter with AND Logic)
```sql
SELECT DISTINCT b.*
FROM books b
-- Full-text keyword search
WHERE MATCH(b.title, b.subtitle, b.description) AGAINST ('+keyword1 +keyword2' IN BOOLEAN MODE)
  -- Access level filter
  AND b.access_level IN ('full', 'limited')
  AND b.is_active = 1
  -- Genre filter (must have at least one selected genre)
  AND EXISTS (
      SELECT 1 FROM book_classifications bc
      JOIN classification_values cv ON bc.classification_value_id = cv.id
      JOIN classification_types ct ON cv.classification_type_id = ct.id
      WHERE bc.book_id = b.id
        AND ct.slug = 'genre'
        AND cv.id IN (1, 2, 3) -- selected genre IDs
  )
  -- Type filter (must have at least one selected type)
  AND EXISTS (
      SELECT 1 FROM book_classifications bc2
      JOIN classification_values cv2 ON bc2.classification_value_id = cv2.id
      JOIN classification_types ct2 ON cv2.classification_type_id = ct2.id
      WHERE bc2.book_id = b.id
        AND ct2.slug = 'type'
        AND cv2.id IN (4, 5) -- selected type IDs
  )
  -- Language filter
  AND EXISTS (
      SELECT 1 FROM book_languages bl
      WHERE bl.book_id = b.id
        AND bl.language_id IN (1, 2) -- selected language IDs
  )
  -- Geographic filter
  AND EXISTS (
      SELECT 1 FROM book_locations bloc
      WHERE bloc.book_id = b.id
        AND bloc.location_id IN (10, 11) -- selected location IDs
  )
ORDER BY b.title ASC
LIMIT 10 OFFSET 0;
```

---

## 📊 Migration Strategy

### Phase 1: Create New Tables (Non-Breaking Changes)
**Objective:** Add new tables without touching existing data

1. Create `creators` table
2. Create `book_creators` table
3. Create `book_languages` table
4. Create `classification_types` table
5. Create `classification_values` table
6. Create `book_classifications` table
7. Create `geographic_locations` table
8. Create `book_locations` table
9. Create `book_relationships` table
10. Create `book_files` table
11. Create `library_references` table
12. Alter `books` table: ADD new columns (don't drop existing yet)
13. Alter `publishers` table: ADD `program_name` column

**Status:** Safe to run - no data loss

---

### Phase 2: Enhance Existing Tables
**Objective:** Add new fields to existing tables

1. Add to `books`:
   - `internal_id`
   - `palm_code`
   - `translated_title`
   - `physical_type`
   - `toc`
   - `notes_issue`
   - `notes_content`
   - `contact`

2. Add to `publishers`:
   - `program_name`

**Status:** Safe to run - only additions

---

### Phase 3: Data Migration Scripts
**Objective:** Migrate existing data to new structure

1. **Migrate Authors:**
   ```sql
   -- Copy authors to creators
   INSERT INTO creators (id, name, biography, birth_year, death_year, nationality, website, created_at, updated_at)
   SELECT id, name, biography, birth_year, death_year, nationality, website, created_at, updated_at
   FROM authors;

   -- Copy book_authors to book_creators
   INSERT INTO book_creators (book_id, creator_id, creator_type, role_description, sort_order, created_at, updated_at)
   SELECT book_id, author_id,
          CASE role
              WHEN 'author' THEN 'author'
              WHEN 'co-author' THEN 'author'
              WHEN 'editor' THEN 'editor'
              WHEN 'translator' THEN 'translator'
          END,
          NULL, -- role_description
          sort_order,
          created_at,
          updated_at
   FROM book_authors;
   ```

2. **Migrate Languages:**
   ```sql
   -- Create book_languages entries from single language_id
   INSERT INTO book_languages (book_id, language_id, is_primary, created_at, updated_at)
   SELECT id, language_id, TRUE, created_at, updated_at
   FROM books
   WHERE language_id IS NOT NULL;
   ```

3. **Migrate Files:**
   ```sql
   -- Migrate primary PDFs
   INSERT INTO book_files (book_id, file_type, file_path, filename, file_size, is_primary, created_at, updated_at)
   SELECT id, 'pdf', pdf_file, pdf_file, file_size, TRUE, created_at, updated_at
   FROM books
   WHERE pdf_file IS NOT NULL;

   -- Migrate thumbnails
   INSERT INTO book_files (book_id, file_type, file_path, filename, is_primary, created_at, updated_at)
   SELECT id, 'thumbnail', cover_image, cover_image, TRUE, created_at, updated_at
   FROM books
   WHERE cover_image IS NOT NULL;
   ```

4. **Migrate Book Editions to Relationships:**
   ```sql
   INSERT INTO book_relationships (book_id, related_book_id, relationship_type, description, created_at, updated_at)
   SELECT parent_book_id, edition_book_id, 'same_version', notes, created_at, updated_at
   FROM book_editions;
   ```

**Status:** Requires testing on staging environment first

---

### Phase 4: Cleanup (After Testing & Verification)
**Objective:** Remove deprecated columns and tables

1. Drop old tables:
   - `DROP TABLE book_authors;`
   - `DROP TABLE authors;`
   - `DROP TABLE book_editions;`

2. Remove deprecated columns from `books`:
   - `ALTER TABLE books DROP COLUMN language_id;`
   - `ALTER TABLE books DROP COLUMN pdf_file;`
   - `ALTER TABLE books DROP COLUMN cover_image;`
   - `ALTER TABLE books DROP COLUMN file_size;`

**Status:** Only run after complete testing and data verification

---

## ✅ Benefits of New Schema

### 1. **Complete CSV Coverage**
- ✅ All 64 metadata fields supported
- ✅ No data loss during import
- ✅ Future-proof for additional fields

### 2. **Normalized Structure**
- ✅ Eliminates data redundancy
- ✅ Single source of truth for each entity
- ✅ Easy to maintain and update

### 3. **Flexible Taxonomy**
- ✅ Add new classification types without schema changes
- ✅ Hierarchical classification support (Genre → Sub-genre)
- ✅ Easy to extend for future requirements

### 4. **Search Optimized**
- ✅ Full-text indexes on all searchable fields
- ✅ Composite indexes for multi-filter queries
- ✅ Efficient AND logic for filters
- ✅ Optimized for 2,000+ book catalog

### 5. **Multilingual Support**
- ✅ Books can have multiple languages
- ✅ Primary vs secondary language distinction
- ✅ ISO code tracking per language

### 6. **Complex Creator Relationships**
- ✅ Unlimited authors, illustrators, editors
- ✅ Custom role descriptions
- ✅ Maintains creator sequence

### 7. **Multiple File Versions**
- ✅ Primary + alternative PDFs/thumbnails
- ✅ Audio/video file support
- ✅ External media links (YouTube/Vimeo)
- ✅ Per-file source tracking

### 8. **Comprehensive Book Relationships**
- ✅ 4 relationship types from CSV
- ✅ Relationship grouping via codes
- ✅ Automatic related content discovery

### 9. **Library Integration**
- ✅ Multiple library references per book
- ✅ Direct catalog links
- ✅ Call number tracking

### 10. **Admin Panel Ready**
- ✅ Clear entity separation for FilamentPHP resources
- ✅ Easy CRUD operations
- ✅ Logical data organization

---

## 🚀 Next Steps & Implementation Plan

### Step 1: Review & Approval ⏳
**Duration:** 1-2 days
**Tasks:**
- [ ] Review this proposal
- [ ] Identify any missing requirements
- [ ] Approve schema design
- [ ] Clarify any questions

**Deliverable:** Approved database schema

---

### Step 2: Create Migration Files ⏳
**Duration:** 1 day
**Tasks:**
- [ ] Generate all Laravel migration files
- [ ] Ensure proper migration order (foreign key dependencies)
- [ ] Add proper indexes and constraints
- [ ] Include rollback methods

**Deliverable:** Complete set of migration files in `database/migrations/`

**Files to Create:**
1. `2025_10_21_000001_create_creators_table.php`
2. `2025_10_21_000002_create_book_creators_table.php`
3. `2025_10_21_000003_create_book_languages_table.php`
4. `2025_10_21_000004_create_classification_types_table.php`
5. `2025_10_21_000005_create_classification_values_table.php`
6. `2025_10_21_000006_create_book_classifications_table.php`
7. `2025_10_21_000007_create_geographic_locations_table.php`
8. `2025_10_21_000008_create_book_locations_table.php`
9. `2025_10_21_000009_create_book_relationships_table.php`
10. `2025_10_21_000010_create_book_files_table.php`
11. `2025_10_21_000011_create_library_references_table.php`
12. `2025_10_21_000012_enhance_books_table.php`
13. `2025_10_21_000013_enhance_publishers_table.php`

---

### Step 3: Create/Update Eloquent Models ⏳
**Duration:** 1 day
**Tasks:**
- [ ] Create new models with relationships
- [ ] Update existing models
- [ ] Define fillable fields and casts
- [ ] Add model scopes for common queries

**Deliverable:** Updated models in `app/Models/`

**Models to Create/Update:**
1. `Creator.php` (new)
2. `BookCreator.php` (new)
3. `BookLanguage.php` (new)
4. `ClassificationType.php` (new)
5. `ClassificationValue.php` (new)
6. `BookClassification.php` (new)
7. `GeographicLocation.php` (new)
8. `BookLocation.php` (new)
9. `BookRelationship.php` (new)
10. `BookFile.php` (new)
11. `LibraryReference.php` (new)
12. `Book.php` (update relationships)
13. `Publisher.php` (update)
14. `Collection.php` (update)
15. `Language.php` (update)

---

### Step 4: Create Database Seeders ⏳
**Duration:** 1 day
**Tasks:**
- [ ] Create seeder for classification types (Purpose, Genre, Type, etc.)
- [ ] Create seeder for languages with ISO codes
- [ ] Create seeder for geographic locations
- [ ] Create test data seeder (10 sample books)

**Deliverable:** Seeders in `database/seeders/`

**Seeders to Create:**
1. `ClassificationTypeSeeder.php`
2. `LanguageSeeder.php`
3. `GeographicLocationSeeder.php`
4. `SampleBooksSeeder.php`

---

### Step 5: Run Migrations & Test ⏳
**Duration:** 1 day
**Tasks:**
- [ ] Backup current database
- [ ] Run migrations on development environment
- [ ] Verify all tables created correctly
- [ ] Run seeders to populate initial data
- [ ] Test relationships in Tinker
- [ ] Verify indexes created

**Deliverable:** Fully migrated database with test data

**Commands:**
```bash
# Backup current database
docker-compose exec app php artisan db:backup

# Run migrations
docker-compose exec app php artisan migrate

# Run seeders
docker-compose exec app php artisan db:seed

# Test in Tinker
docker-compose exec app php artisan tinker
```

---

### Step 6: Create FilamentPHP Resources ⏳
**Duration:** 2-3 days
**Tasks:**
- [ ] Create Filament resources for all new entities
- [ ] Define form fields and validation
- [ ] Configure table columns and filters
- [ ] Set up relationship managers
- [ ] Add bulk actions

**Deliverable:** Admin panel resources in `app/Filament/Resources/`

**Resources to Create:**
1. `CreatorResource.php`
2. `ClassificationTypeResource.php`
3. `ClassificationValueResource.php`
4. `GeographicLocationResource.php`
5. `BookResource.php` (major update)
6. `PublisherResource.php` (update)
7. `CollectionResource.php` (update)
8. `LanguageResource.php` (update)

---

### Step 7: Create CSV Import Script ⏳
**Duration:** 2-3 days
**Tasks:**
- [ ] Create CSV parser for 64 fields
- [ ] Map CSV columns to database fields
- [ ] Handle relationships (creators, languages, classifications)
- [ ] Validate data before import
- [ ] Create progress reporting
- [ ] Add error handling and logging

**Deliverable:** Import command in `app/Console/Commands/ImportBooksFromCsv.php`

**Import Process:**
1. Parse CSV file
2. Create/find creators (authors, illustrators, editors)
3. Create/find classifications, languages, locations
4. Create book record
5. Link relationships (book_creators, book_languages, etc.)
6. Import files (PDFs, thumbnails)
7. Create book relationships
8. Log success/errors

---

### Step 8: Update Search & Filter Logic ⏳
**Duration:** 2 days
**Tasks:**
- [ ] Update search queries to use new schema
- [ ] Implement multi-filter AND logic
- [ ] Optimize queries with proper eager loading
- [ ] Add search result caching if needed

**Deliverable:** Updated search functionality

---

### Step 9: Testing & Validation ⏳
**Duration:** 2-3 days
**Tasks:**
- [ ] Test admin panel CRUD operations
- [ ] Test search with various filter combinations
- [ ] Test CSV import with sample data
- [ ] Verify all relationships work correctly
- [ ] Performance testing with 2,000+ books
- [ ] Edge case testing

**Deliverable:** Tested and validated system

---

### Step 10: Documentation ⏳
**Duration:** 1 day
**Tasks:**
- [ ] Update CLAUDE.md with new schema
- [ ] Create database diagram
- [ ] Document import process
- [ ] Create admin user guide

**Deliverable:** Complete documentation

---

## 📈 Timeline Estimate

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| 1. Review & Approval | 1-2 days | None |
| 2. Migration Files | 1 day | Phase 1 |
| 3. Eloquent Models | 1 day | Phase 2 |
| 4. Database Seeders | 1 day | Phase 3 |
| 5. Run Migrations | 1 day | Phase 4 |
| 6. Filament Resources | 2-3 days | Phase 5 |
| 7. CSV Import Script | 2-3 days | Phase 6 |
| 8. Search & Filters | 2 days | Phase 7 |
| 9. Testing | 2-3 days | Phase 8 |
| 10. Documentation | 1 day | Phase 9 |
| **Total** | **14-18 days** | Sequential |

---

## ❓ Questions for Clarification

### 1. Classification Values
**Question:** Do you have a complete list of values for each classification type (Purpose, Genre, Sub-genre, Type, Themes/Uses, Learner Level)?
**Impact:** Needed for seeder creation

### 2. Geographic Locations
**Question:** What are all the islands and states that should be included?
**Impact:** Needed for seeder creation

### 3. VLA Standards
**Question:** How should VLA Standard and VLA Benchmark be handled? Are they internal-only or displayed to users?
**Impact:** Determines if they need separate tables or can be simple text fields

### 4. Relationship Codes
**Question:** In the CSV, how are the "same code" relationship groupings actually stored? As a shared ID or text code?
**Impact:** Determines `relationship_code` field type and indexing

### 5. Alternative Files
**Question:** How common are alternative PDFs/thumbnails? Should we prioritize this in Phase 1 or defer?
**Impact:** Can simplify initial migration if rare

### 6. Existing Data
**Question:** Do you have any existing data in the current database that needs to be preserved?
**Impact:** Determines if we need data migration or fresh start

---

## 🎯 Recommendation: Proceed?

I recommend we proceed with this schema design because it:

1. ✅ **Fully supports your CSV structure** - All 64 fields mapped
2. ✅ **Enables efficient search** - Proper indexing and normalized structure
3. ✅ **Scales to 2,000+ books** - Optimized for your catalog size
4. ✅ **Flexible for future growth** - Easy to extend without breaking changes
5. ✅ **Admin-friendly** - Clear structure for FilamentPHP resources
6. ✅ **Maintains data integrity** - Proper foreign keys and constraints

**Next immediate action:** If approved, I'll start generating the Laravel migration files.

---

## 📝 Approval Signature

- [ ] Schema approved as-is
- [ ] Schema approved with modifications (see notes below)
- [ ] Need more information before approving

**Notes/Modifications:**
_[Add any requested changes here]_

**Approved by:** ________________
**Date:** ________________

---

**End of Proposal**
