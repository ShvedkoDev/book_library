# Database Structure Draft
## Micronesian Teachers Digital Library

### Overview
This document outlines the proposed database structure for the digital library system that will manage ~2,000 educational books with search, filtering, and user management capabilities.

---

## Core Tables

### 1. **users**
Laravel's default user table with extensions for our needs.

```sql
- id (bigint, primary key)
- name (string, 255)
- email (string, 255, unique)
- email_verified_at (timestamp, nullable)
- password (string, 255)
- role (enum: 'user', 'admin', default: 'user')
- terms_accepted_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: User authentication, role management, terms acceptance tracking

---

### 2. **languages**
Manage multiple languages for the library content.

```sql
- id (bigint, primary key)
- code (string, 10, unique) -- e.g., 'en', 'chuukese', 'pohnpeian'
- name (string, 100) -- e.g., 'English', 'Chuukese', 'Pohnpeian'
- native_name (string, 100, nullable) -- Native language name
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Support for multiple Micronesian languages and English

---

### 3. **categories**
Hierarchical categorization system for books.

```sql
- id (bigint, primary key)
- name (string, 100)
- slug (string, 100, unique)
- description (text, nullable)
- parent_id (bigint, nullable, foreign key -> categories.id)
- sort_order (integer, default: 0)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Organize books by subject, grade level, type (e.g., Science > Grade 3 > Biology)

---

### 4. **authors**
Author information for books.

```sql
- id (bigint, primary key)
- name (string, 255)
- biography (text, nullable)
- birth_year (integer, nullable)
- death_year (integer, nullable)
- nationality (string, 100, nullable)
- website (string, 255, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Store author information and enable "more by this author" features

---

### 5. **publishers**
Publisher information.

```sql
- id (bigint, primary key)
- name (string, 255)
- address (text, nullable)
- website (string, 255, nullable)
- contact_email (string, 255, nullable)
- established_year (integer, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Track publishing information and enable publisher-based filtering

---

### 6. **collections**
Group related books into collections.

```sql
- id (bigint, primary key)
- name (string, 255)
- description (text, nullable)
- is_series (boolean, default: false) -- true for book series
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Group books by series, curriculum sets, or thematic collections

---

## Main Content Tables

### 7. **books**
Core book information table.

```sql
- id (bigint, primary key)
- title (string, 500)
- subtitle (string, 500, nullable)
- isbn (string, 20, nullable, unique)
- isbn13 (string, 20, nullable, unique)
- language_id (bigint, foreign key -> languages.id)
- publisher_id (bigint, nullable, foreign key -> publishers.id)
- collection_id (bigint, nullable, foreign key -> collections.id)
- publication_year (integer, nullable)
- edition (string, 50, nullable)
- pages (integer, nullable)
- description (text, nullable)
- cover_image (string, 500, nullable) -- path to cover image
- pdf_file (string, 500, nullable) -- path to PDF file
- file_size (bigint, nullable) -- PDF file size in bytes
- access_level (enum: 'full', 'limited', 'unavailable', default: 'unavailable')
- is_featured (boolean, default: false)
- view_count (integer, default: 0)
- download_count (integer, default: 0)
- sort_order (integer, default: 0)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Store all book metadata and access control information

---

### 8. **book_authors**
Many-to-many relationship between books and authors.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- author_id (bigint, foreign key -> authors.id, on delete cascade)
- role (enum: 'author', 'co-author', 'editor', 'translator', default: 'author')
- sort_order (integer, default: 0)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Handle books with multiple authors and different author roles

---

### 9. **book_categories**
Many-to-many relationship between books and categories.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- category_id (bigint, foreign key -> categories.id, on delete cascade)
- is_primary (boolean, default: false) -- one primary category per book
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Allow books to belong to multiple categories with one primary category

---

### 10. **book_editions**
Track different editions of the same book.

```sql
- id (bigint, primary key)
- parent_book_id (bigint, foreign key -> books.id) -- original book
- edition_book_id (bigint, foreign key -> books.id) -- edition book
- edition_type (enum: 'revised', 'updated', 'translated', 'abridged', 'other')
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Link different editions and enable edition navigation

---

## User Interaction Tables

### 11. **book_ratings**
User ratings for books.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- user_id (bigint, foreign key -> users.id, on delete cascade)
- rating (tinyint, 1-5) -- star rating
- created_at (timestamp)
- updated_at (timestamp)

UNIQUE KEY (book_id, user_id) -- one rating per user per book
```

**Purpose**: Allow registered users to rate books

---

### 12. **book_reviews**
User reviews for books.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- user_id (bigint, foreign key -> users.id, on delete cascade)
- review_text (text)
- is_approved (boolean, default: false)
- approved_by (bigint, nullable, foreign key -> users.id)
- approved_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Community reviews with moderation capability

---

### 13. **book_bookmarks**
User bookmarks/favorites.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- user_id (bigint, foreign key -> users.id, on delete cascade)
- created_at (timestamp)
- updated_at (timestamp)

UNIQUE KEY (book_id, user_id) -- one bookmark per user per book
```

**Purpose**: Allow users to bookmark favorite books

---

## Activity Tracking Tables

### 14. **book_views**
Track book page views for analytics.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- user_id (bigint, nullable, foreign key -> users.id, on delete set null)
- ip_address (string, 45, nullable) -- support IPv6
- user_agent (text, nullable)
- viewed_at (timestamp, default: current_timestamp)

INDEX (book_id, viewed_at)
INDEX (user_id, viewed_at)
```

**Purpose**: Analytics and "recently viewed" features

---

### 15. **book_downloads**
Track PDF downloads.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- user_id (bigint, nullable, foreign key -> users.id, on delete set null)
- ip_address (string, 45, nullable)
- user_agent (text, nullable)
- downloaded_at (timestamp, default: current_timestamp)

INDEX (book_id, downloaded_at)
INDEX (user_id, downloaded_at)
```

**Purpose**: Track download statistics and usage patterns

---

## Search and Metadata Tables

### 16. **book_identifiers**
Additional identifiers for books (DOI, OCLC, etc.).

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- identifier_type (enum: 'doi', 'oclc', 'lccn', 'other')
- identifier_value (string, 255)
- created_at (timestamp)
- updated_at (timestamp)

UNIQUE KEY (book_id, identifier_type, identifier_value)
```

**Purpose**: Store various book identifiers for better cataloging

---

### 17. **book_keywords**
Keywords/tags for enhanced search.

```sql
- id (bigint, primary key)
- book_id (bigint, foreign key -> books.id, on delete cascade)
- keyword (string, 100)
- created_at (timestamp)
- updated_at (timestamp)

INDEX (keyword)
INDEX (book_id, keyword)
```

**Purpose**: Improve search functionality with additional keywords

---

## System Tables

### 18. **terms_of_use_versions**
Track different versions of terms of use.

```sql
- id (bigint, primary key)
- version (string, 20)
- content (longtext)
- is_active (boolean, default: false)
- effective_date (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
```

**Purpose**: Version control for terms of use with user acceptance tracking

---

### 19. **user_terms_acceptance**
Track which terms version each user accepted.

```sql
- id (bigint, primary key)
- user_id (bigint, foreign key -> users.id, on delete cascade)
- terms_version_id (bigint, foreign key -> terms_of_use_versions.id)
- accepted_at (timestamp)
- ip_address (string, 45, nullable)

UNIQUE KEY (user_id, terms_version_id)
```

**Purpose**: Legal compliance and terms version tracking

---

## Database Indexes and Performance

### Recommended Indexes
```sql
-- Search performance
INDEX idx_books_title ON books(title);
INDEX idx_books_language_active ON books(language_id, is_active);
INDEX idx_books_access_active ON books(access_level, is_active);
INDEX idx_books_publication_year ON books(publication_year);

-- Category performance
INDEX idx_categories_parent ON categories(parent_id, is_active);
INDEX idx_book_categories_category ON book_categories(category_id);

-- User activity
INDEX idx_book_ratings_rating ON book_ratings(rating);
INDEX idx_book_reviews_approved ON book_reviews(is_approved);

-- Full-text search
FULLTEXT INDEX ft_books_search ON books(title, subtitle, description);
FULLTEXT INDEX ft_authors_search ON authors(name, biography);
```

---

## Estimated Data Volume

| Table | Estimated Rows | Notes |
|-------|---------------|-------|
| books | 2,000 | Initial dataset |
| authors | 500-800 | Many books share authors |
| categories | 50-100 | Hierarchical structure |
| languages | 10-15 | Micronesian languages + English |
| publishers | 100-200 | Educational publishers |
| users | 1,000+ | Teachers and students |
| book_views | 50,000+ | High volume tracking |
| book_downloads | 10,000+ | Download tracking |

---

## Data Import Considerations

### Excel Import Mapping
When importing from the existing Excel spreadsheet:

1. **Book Title** → `books.title`
2. **Author** → Parse into `authors` table and link via `book_authors`
3. **Publisher** → `publishers.name` and link via `books.publisher_id`
4. **Year** → `books.publication_year`
5. **Language** → Map to `languages.code` and link via `books.language_id`
6. **Category/Subject** → Parse into `categories` and link via `book_categories`
7. **ISBN** → `books.isbn` or `books.isbn13`
8. **Description** → `books.description`
9. **Access Status** → `books.access_level`

### Data Validation Rules
- All books must have at least one category
- Books with `access_level = 'full'` or `'limited'` should have PDF files
- ISBNs must be unique when provided
- Author names should be normalized to avoid duplicates

---

This structure provides flexibility for the current ~2,000 books while supporting future growth and enhanced features like multilingual content, user interactions, and comprehensive search capabilities.