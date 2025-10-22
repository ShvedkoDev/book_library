# TODO: Library Pages Implementation

## Overview
Implement public-facing library pages for browsing and viewing books based on the HTML design templates in `public/ui-test/final/`:
- **Library Listing Page** (`library.html`) - Browse all books with search and filters
- **Book Detail Page** (`book.html`) - Individual book pages with metadata and files

## Prerequisites
- ✅ Database schema complete with books, files, classifications, languages, etc.
- ✅ Filament admin panel functional for managing books
- ✅ UI templates available in `public/ui-test/final/`

---

## Phase 1: Setup & Architecture

### 1.1 Create Routes
**File:** `routes/web.php`

- [ ] Create library index route: `GET /library`
- [ ] Create book detail route: `GET /library/book/{slug}` or `GET /library/book/{id}`
- [ ] Consider SEO-friendly slugs for books (auto-generated from title)

### 1.2 Create Controllers
**Files:** `app/Http/Controllers/`

- [ ] Create `LibraryController.php`
  - `index()` - List all books with filters/search
  - `show($slug)` - Display single book detail
- [ ] Add proper dependency injection for models
- [ ] Implement authorization checks (if needed for different access levels)

### 1.3 Create Livewire Components (Recommended Approach)
**Files:** `app/Livewire/`

- [ ] Create `Library/BookList.php` component for the listing page
  - Handle real-time search
  - Handle filter interactions
  - Handle pagination
  - Handle sorting
- [ ] Create `Library/BookDetail.php` component for book page (optional, if interactive features needed)
- [ ] Create `Library/BookFilters.php` sub-component for the sidebar filters
- [ ] Create `Library/SearchBar.php` sub-component

**OR** use traditional Blade templates if no real-time interactivity needed.

---

## Phase 2: Library Listing Page (`/library`)

### 2.1 Backend - Controller Logic
**File:** `app/Http/Controllers/LibraryController.php` or `app/Livewire/Library/BookList.php`

- [ ] Query all active books (`is_active = true`)
- [ ] Implement keyword search across:
  - Book title
  - Book description
  - Creator names
  - Keywords
  - Publisher name
  - Collection name
- [ ] Implement filter logic:
  - **Subject** (from classification types - Purpose/Genre)
  - **Grade Level** (from learner level classifications)
  - **Resource Type** (from type classifications or physical_type)
  - **Language** (from languages relationship)
  - **Publication Year** (from publication_year)
- [ ] Implement sorting options:
  - Relevance (default with search)
  - Title A-Z
  - Author/Creator
  - Date Added (created_at)
  - Most Popular (view_count)
- [ ] Implement pagination (10, 20, 50, 100 items per page)
- [ ] Eager load relationships to avoid N+1 queries:
  ```php
  Book::with(['languages', 'creators', 'publisher', 'collection', 'files'])
  ```

### 2.2 Frontend - Blade View
**File:** `resources/views/library/index.blade.php`

- [ ] Copy HTML structure from `public/ui-test/final/library.html`
- [ ] Create master layout if not exists: `resources/views/layouts/app.blade.php`
  - Include header with logos and navigation
  - Include Guide/Library toggle
  - Include language selector (placeholder)
  - Include footer
- [ ] Replace static HTML with dynamic Blade/Livewire:
  - [ ] Results count display
  - [ ] Book table/grid with data from database
  - [ ] Thumbnail images (from book files where `file_type = 'thumbnail'`)
  - [ ] Book title (linked to detail page)
  - [ ] Metadata display (date, publisher)
  - [ ] Description (from genres, languages, access level)
  - [ ] Action buttons (Locate/View button)
- [ ] Implement search input with Livewire `wire:model.live` or Alpine.js
- [ ] Implement filter checkboxes with auto-filter on change
- [ ] Implement entries per page dropdown
- [ ] Implement pagination controls
- [ ] Implement sort dropdown

### 2.3 Search & Filter Logic
**Implementation in Livewire or Controller**

- [ ] Keyword search implementation:
  ```php
  $query->where(function($q) use ($search) {
      $q->where('title', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->orWhereHas('creators', fn($q) => $q->where('name', 'like', "%{$search}%"))
        ->orWhereHas('publisher', fn($q) => $q->where('name', 'like', "%{$search}%"));
  });
  ```
- [ ] Filter by classifications (AND logic for multiple filters):
  ```php
  if (!empty($filters['subject'])) {
      $query->whereHas('purposeClassifications', fn($q) =>
          $q->whereIn('slug', $filters['subject'])
      );
  }
  ```
- [ ] Filter by languages:
  ```php
  if (!empty($filters['languages'])) {
      $query->whereHas('languages', fn($q) =>
          $q->whereIn('slug', $filters['languages'])
      );
  }
  ```
- [ ] Clear filters functionality
- [ ] Toggle filter groups (collapsed/expanded)

### 2.4 Styling & Assets
**Files:** `resources/css/` and `public/ui-test/final/assets/`

- [ ] Copy CSS from `public/ui-test/final/assets/css/` to Laravel assets
- [ ] Integrate with Laravel Mix/Vite:
  - [ ] Add to `resources/css/app.css` or create separate library CSS
  - [ ] Compile and version assets
- [ ] Copy images from `public/ui-test/final/assets/images/` to `public/images/`
- [ ] Update image paths in Blade templates
- [ ] Include Font Awesome for icons
- [ ] Ensure responsive design works on mobile

---

## Phase 3: Book Detail Page (`/library/book/{slug}`)

### 3.1 Backend - Controller Logic
**File:** `app/Http/Controllers/LibraryController.php` or `app/Livewire/Library/BookDetail.php`

- [ ] Find book by slug or ID
- [ ] Eager load all relationships:
  ```php
  Book::with([
      'languages',
      'creators',
      'publisher',
      'collection',
      'files',
      'geographicLocations',
      'purposeClassifications',
      'genreClassifications',
      'subgenreClassifications',
      'typeClassifications',
      'themesClassifications',
      'learnerLevelClassifications',
      'libraryReferences',
      'bookRelationships.relatedBook'
  ])->findOrFail($id);
  ```
- [ ] Increment view counter:
  ```php
  $book->increment('view_count');
  ```
- [ ] Get related books:
  - [ ] Books in same collection
  - [ ] Books in same language(s)
  - [ ] Books by same creator(s)
  - [ ] Books with same classification
- [ ] Calculate average rating (if ratings system implemented)
- [ ] Return 404 if book not found or not active

### 3.2 Frontend - Blade View
**File:** `resources/views/library/show.blade.php`

Copy structure from `public/ui-test/final/book.html` and implement:

#### Left Sidebar - Book Cover & Actions
- [ ] Display book cover image (from files where `file_type = 'thumbnail'` and `is_primary = true`)
- [ ] Display access status badge:
  - Full Access (green)
  - Limited Access (yellow)
  - Unavailable (red)
- [ ] Action buttons based on access level:
  - [ ] **View PDF** button (if PDF file exists and access_level = 'full')
  - [ ] **Download PDF** button (if access_level = 'full')
  - [ ] **Preview** button (if access_level = 'limited')
  - [ ] **Request Access** button (if access_level = 'unavailable')
  - [ ] **Listen to Audio** button (if audio file exists)
  - [ ] **Watch Video** link (if external_url for video exists)
- [ ] Display star rating (average from reviews)
- [ ] User actions (if logged in):
  - [ ] Add to favorites
  - [ ] Share book
  - [ ] Rate this book
  - [ ] Write a review

#### Main Content Area
- [ ] **Breadcrumbs** navigation (Home > Library > Book Title)
- [ ] **Book Title** (h1)
- [ ] **Subtitle** (if exists)
- [ ] **Translated Title** (if exists)

- [ ] **Metadata Section** (table or definition list):
  - [ ] Author(s) / Creator(s) with roles
  - [ ] Publisher
  - [ ] Collection (if exists)
  - [ ] Publication Year
  - [ ] Pages
  - [ ] Language(s)
  - [ ] Physical Type (Book, Journal, Workbook, etc.)
  - [ ] PALM Code (if exists)
  - [ ] Internal ID

- [ ] **Classifications Section**:
  - [ ] Purpose (subject areas)
  - [ ] Genre
  - [ ] Sub-genre
  - [ ] Type
  - [ ] Themes/Uses
  - [ ] Learner Level (grade levels)

- [ ] **Educational Standards** (if exist):
  - [ ] VLA Standard
  - [ ] VLA Benchmark

- [ ] **Description / Abstract** (formatted text)
- [ ] **Table of Contents** (if exists, formatted as list)

- [ ] **Geographic Locations** (islands/states covered)

- [ ] **Keywords** (displayed as tags/badges)

- [ ] **Notes** section:
  - [ ] Notes - Issue
  - [ ] Notes - Content

- [ ] **Contact / Ordering Info** (if exists for hard copy)

#### Related Books Sections
- [ ] **Other Editions** (from bookRelationships where type = 'same_version')
- [ ] **Books in Same Collection**
- [ ] **Books in Same Language**
- [ ] **Books in Other Languages** (translations)
- [ ] **Supporting Materials** (from bookRelationships where type = 'supporting')
- [ ] Display as horizontal card grid with:
  - Thumbnail
  - Title (linked)
  - Basic metadata

#### Library References Section
- [ ] Display physical copy locations (UH Library, COM Library)
  - Library name
  - Reference number
  - Call number
  - Catalog link
  - Notes

#### Reviews & Ratings Section (Optional for later)
- [ ] Display existing reviews
- [ ] Add review form (if logged in)
- [ ] Rating histogram

### 3.3 File Handling & Access Control

- [ ] Implement PDF viewer route: `GET /library/book/{id}/view-pdf/{fileId}`
  - Check access level
  - Stream PDF or embed viewer
  - Track views
- [ ] Implement download route: `GET /library/book/{id}/download/{fileId}`
  - Check access level
  - Force download
  - Track downloads
- [ ] Implement audio player (if audio files exist)
  - Use HTML5 audio player
- [ ] Implement video embedding (for external URLs)
  - YouTube/Vimeo embed

### 3.4 SEO & Metadata
**In book detail view**

- [ ] Dynamic page title: `{Book Title} - Micronesian Teachers Digital Library`
- [ ] Meta description from book description
- [ ] Open Graph tags for social sharing:
  - og:title
  - og:description
  - og:image (book cover)
  - og:type (book)
- [ ] Schema.org structured data (Book schema):
  ```json
  {
    "@context": "https://schema.org",
    "@type": "Book",
    "name": "Book Title",
    "author": {...},
    "publisher": {...},
    "datePublished": "2024",
    ...
  }
  ```

---

## Phase 4: Additional Features

### 4.1 Navigation & Layout
**Files:** `resources/views/layouts/app.blade.php`, `resources/views/components/`

- [ ] Create reusable header component:
  - [ ] Top bar with institution logos
  - [ ] Guide/Library toggle (active state based on current route)
  - [ ] Language selector (placeholder for now)
  - [ ] Main navigation menu
  - [ ] Search bar
  - [ ] Login link
- [ ] Create reusable footer component
- [ ] Create breadcrumbs component
- [ ] Implement Terms of Use modal (from library.html)

### 4.2 Thumbnail Generation
**For books without thumbnail images**

- [ ] Create service to generate placeholder thumbnails
- [ ] Option 1: Use first page of PDF as thumbnail (if PDF processing enabled)
- [ ] Option 2: Generate colored placeholder with first letter of title
- [ ] Option 3: Default book icon placeholder

### 4.3 Performance Optimization

- [ ] Implement query result caching for popular searches
- [ ] Add database indexes:
  ```php
  // In book migration
  $table->index('title');
  $table->index('is_active');
  $table->index(['is_active', 'publication_year']);
  $table->index('access_level');
  ```
- [ ] Lazy load images in book grid
- [ ] Implement infinite scroll or AJAX pagination (optional)
- [ ] Cache aggregated filter options (languages, classifications available)

### 4.4 Analytics & Tracking

- [ ] Track book views (already has view_count column)
- [ ] Track file downloads
- [ ] Track search queries (for improving search)
- [ ] Track popular filters
- [ ] Add Google Analytics or similar (if required)

---

## Phase 5: Testing & Quality Assurance

### 5.1 Functionality Testing
- [ ] Test search with various keywords
- [ ] Test all filter combinations
- [ ] Test pagination with different page sizes
- [ ] Test sorting options
- [ ] Test book detail page for books with:
  - All fields populated
  - Minimal fields populated
  - No files attached
  - Multiple files attached
  - Different access levels
- [ ] Test related books display
- [ ] Test file viewing/downloading
- [ ] Test responsive design on mobile devices

### 5.2 Data Validation
- [ ] Verify all database relationships are correct
- [ ] Test with production-like data (~2000 books)
- [ ] Test edge cases:
  - Books with no creator
  - Books with no publisher
  - Books with no thumbnail
  - Very long titles
  - Special characters in titles

### 5.3 Performance Testing
- [ ] Test page load time with 2000+ books
- [ ] Check query performance (use Laravel Debugbar)
- [ ] Optimize N+1 queries
- [ ] Test concurrent user access

### 5.4 Browser Compatibility
- [ ] Test on Chrome
- [ ] Test on Firefox
- [ ] Test on Safari
- [ ] Test on Edge
- [ ] Test on mobile browsers

---

## Phase 6: Deployment & Documentation

### 6.1 Asset Compilation
- [ ] Compile production assets with Vite
  ```bash
  npm run build
  ```
- [ ] Verify all CSS and JS are minified
- [ ] Check that all images are optimized

### 6.2 Database Seeding (Optional)
- [ ] Create seeder for sample books if needed
- [ ] Create seeder for classifications if not already done

### 6.3 Documentation
- [ ] Document route structure
- [ ] Document how to add new filter types
- [ ] Document file access permissions
- [ ] Document how thumbnails are handled
- [ ] Create user guide for library usage

### 6.4 Security Review
- [ ] Ensure no SQL injection vulnerabilities
- [ ] Validate file access permissions
- [ ] Check CSRF protection on forms
- [ ] Sanitize user input (search queries)
- [ ] Implement rate limiting on search/filters (if needed)

---

## Technical Implementation Notes

### Recommended Tech Stack
- **Backend:** Laravel 12.x controllers or Livewire 3.x components
- **Frontend:** Blade templates with Alpine.js for interactivity
- **Search:** Laravel Scout (optional, for advanced search) or Eloquent where clauses
- **Pagination:** Laravel's built-in pagination
- **File Serving:** Laravel storage with symlink or CDN
- **Caching:** Redis or File cache for filter options and popular queries

### Database Queries - Best Practices

```php
// Example efficient query for library listing
$books = Book::query()
    ->select(['id', 'title', 'description', 'publication_year', 'access_level', 'created_at'])
    ->with([
        'languages:id,name,slug',
        'creators:id,name',
        'publisher:id,name',
        'files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true),
    ])
    ->where('is_active', true)
    ->when($search, function($q) use ($search) {
        // Search logic
    })
    ->when($filters, function($q) use ($filters) {
        // Filter logic
    })
    ->orderBy($sortColumn, $sortDirection)
    ->paginate($perPage);
```

### File Structure
```
app/
├── Http/
│   └── Controllers/
│       └── LibraryController.php
├── Livewire/
│   └── Library/
│       ├── BookList.php
│       ├── BookDetail.php
│       ├── BookFilters.php
│       └── SearchBar.php
└── Models/
    └── Book.php (already exists)

resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── components/
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   └── breadcrumbs.blade.php
│   └── library/
│       ├── index.blade.php
│       ├── show.blade.php
│       └── partials/
│           ├── book-card.blade.php
│           ├── filters.blade.php
│           └── related-books.blade.php
├── css/
│   └── library.css
└── js/
    └── library.js

public/
├── css/
│   └── library.css (compiled)
└── images/
    └── library/
        ├── logos/
        └── placeholders/
```

---

## Estimated Timeline
- Phase 1: Setup & Architecture - **2-3 hours**
- Phase 2: Library Listing Page - **8-10 hours**
- Phase 3: Book Detail Page - **8-10 hours**
- Phase 4: Additional Features - **4-6 hours**
- Phase 5: Testing & QA - **4-6 hours**
- Phase 6: Deployment - **2-3 hours**

**Total: 28-38 hours** for complete implementation

---

## Priority Order
1. ✅ **High Priority - MVP:**
   - Basic library listing with search
   - Basic book detail page
   - File viewing/downloading

2. **Medium Priority:**
   - All filters working
   - Related books sections
   - Responsive design

3. **Low Priority - Nice to Have:**
   - Advanced search with Laravel Scout
   - Reviews and ratings
   - User favorites
   - Social sharing
   - Analytics tracking

---

## Success Criteria
- ✅ Users can browse all 2000+ books efficiently
- ✅ Search returns relevant results in < 1 second
- ✅ Filters work correctly with AND logic
- ✅ Book detail pages display all available metadata
- ✅ PDFs can be viewed/downloaded based on access level
- ✅ Design matches HTML templates from `public/ui-test/final/`
- ✅ Pages are mobile-responsive
- ✅ No N+1 query issues
- ✅ SEO-friendly URLs and metadata
