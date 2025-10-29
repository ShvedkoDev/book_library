# CMS Development TODO

## Project Overview
Simple CMS for managing static content pages with WYSIWYG editing, section anchors, resource contributors, and media management.

## Requirements
- **Pages Management**: CRUD for content pages with slug-based URLs
- **WYSIWYG Editor**: Rich text editor for page content
- **Sections/Anchors**: Automatic H2 extraction for table of contents navigation
- **Resource Contributors**: Separate entity with page relationships
- **Media Management**: File management for books (PDFs) and page assets (images, files)
- **Admin Only**: No role system, all FilamentPHP admins have full access

---

## Phase 1: Database Design & Migrations ✅

### 1.1 Pages Table ✅
- [x] Create `pages` migration
  - [x] `id` (primary key)
  - [x] `title` (string, required)
  - [x] `slug` (string, unique, required) - URL-friendly identifier
  - [x] `content` (longText) - HTML content from WYSIWYG
  - [x] `meta_description` (text, nullable) - SEO description
  - [x] `meta_keywords` (text, nullable) - SEO keywords
  - [x] `is_published` (boolean, default true)
  - [x] `published_at` (timestamp, nullable)
  - [x] `order` (integer, default 0) - for menu ordering
  - [x] `parent_id` (foreign key, nullable) - for page hierarchy
  - [x] `created_at`, `updated_at`
  - [x] Add indexes: `slug`, `is_published`, `parent_id`

### 1.2 Resource Contributors Table ✅
- [x] Create `resource_contributors` migration
  - [x] `id` (primary key)
  - [x] `name` (string, required)
  - [x] `organization` (string, nullable)
  - [x] `logo` (string, nullable) - path to logo image
  - [x] `website_url` (string, nullable)
  - [x] `description` (text, nullable)
  - [x] `order` (integer, default 0) - display ordering
  - [x] `is_active` (boolean, default true)
  - [x] `created_at`, `updated_at`

### 1.3 Page-Contributors Pivot Table ✅
- [x] Create `page_resource_contributor` pivot migration
  - [x] `page_id` (foreign key)
  - [x] `resource_contributor_id` (foreign key)
  - [x] `order` (integer, default 0) - display order per page
  - [x] Composite unique index on (`page_id`, `resource_contributor_id`)
  - [x] Foreign key constraints with cascade delete

### 1.4 Page Sections Table (Optional, for caching) ✅
- [x] Create `page_sections` migration (optional)
  - [x] `id` (primary key)
  - [x] `page_id` (foreign key)
  - [x] `heading` (string) - H2 text content
  - [x] `anchor` (string) - URL-friendly anchor (e.g., "how-to-use")
  - [x] `order` (integer) - order of appearance in document
  - [x] `created_at`, `updated_at`
  - [x] Foreign key constraint with cascade delete
  - [x] Note: This can be generated dynamically from content, but storing allows for custom anchors

---

## Phase 2: Models & Relationships ✅

### 2.1 Page Model ✅
- [x] Create `app/Models/Page.php`
  - [x] Add fillable fields
  - [x] Add casts: `published_at` => `datetime`, `is_published` => `boolean`
  - [x] Add `parent()` belongsTo relationship (self-referencing)
  - [x] Add `children()` hasMany relationship (self-referencing)
  - [x] Add `resourceContributors()` belongsToMany relationship
  - [x] Add `sections()` hasMany relationship (if using table)
  - [x] Add slug auto-generation from title (in creating event)
  - [x] Add method `extractSections()` to parse HTML and extract H2 tags
  - [x] Add method `getTableOfContents()` to return sections array
  - [x] Add scope `published()` for filtering published pages
  - [x] Add accessor `excerpt()` for meta description or truncated content

### 2.2 ResourceContributor Model ✅
- [x] Create `app/Models/ResourceContributor.php`
  - [x] Add fillable fields
  - [x] Add casts: `is_active` => `boolean`
  - [x] Add `pages()` belongsToMany relationship
  - [x] Add scope `active()` for filtering active contributors
  - [x] Add accessor for full logo URL

### 2.3 PageSection Model (Optional) ✅
- [x] Create `app/Models/PageSection.php` (if using sections table)
  - [x] Add fillable fields
  - [x] Add `page()` belongsTo relationship
  - [x] Add method to generate anchor from heading

---

## Phase 3: FilamentPHP Admin Resources ✅

### 3.1 Page Resource ✅
- [x] Create `app/Filament/Resources/PageResource.php`
  - [x] Generate with: `php artisan make:filament-resource Page --generate`
  - [x] Configure table columns:
    - [x] Title (searchable, sortable)
    - [x] Slug (searchable, badge)
    - [x] Published status (toggle column)
    - [x] Published date
    - [x] Parent page (if hierarchical)
    - [x] Updated at (date-time)
  - [x] Configure form schema:
    - [x] Section: "Basic Information"
      - [x] TextInput for `title` (required, maxLength 255)
      - [x] TextInput for `slug` (required, unique, alphanumeric-dash)
        - [x] Add slug auto-generation from title
        - [x] Add validation rule for uniqueness
      - [x] Select for `parent_id` (relationship, searchable, nullable)
    - [x] Section: "Content"
      - [x] **RichEditor for `content`** (see Phase 4 for WYSIWYG setup)
        - [x] Toolbar: bold, italic, underline, headings, lists, links, images
        - [x] File uploads enabled
    - [x] Section: "SEO & Metadata"
      - [x] Textarea for `meta_description` (maxLength 160, hint text)
      - [x] Textarea for `meta_keywords` (hint: comma-separated)
    - [x] Section: "Publishing"
      - [x] Toggle for `is_published`
      - [x] DateTimePicker for `published_at`
      - [x] TextInput for `order` (numeric, default 0)
    - [x] Section: "Resource Contributors"
      - [x] **CheckboxList for `resourceContributors` relationship**
        - [x] Select contributor from list
        - [x] Display ordering
  - [x] Configure filters:
    - [x] Published status (ternary: all/published/draft)
    - [x] Parent page (select filter)
  - [x] Configure actions:
    - [x] View action (opens frontend page in new tab)
    - [x] Edit action
    - [x] Delete action
  - [x] Add custom action: "Preview Sections" (shows extracted H2 anchors)
  - [x] Add table action: "Duplicate Page"

### 3.2 Page Resource Pages ✅
- [x] Customize `PageResource/Pages/ListPages.php`
  - [x] Add create action
  - [x] Add bulk delete action
- [x] Customize `PageResource/Pages/CreatePage.php`
  - [x] Add notification on success
- [x] Customize `PageResource/Pages/EditPage.php`
  - [x] Add "View Live Page" header action
  - [x] Add "Preview Sections" action
  - [x] Show last updated timestamp

### 3.3 Resource Contributor Resource ✅
- [x] Create `app/Filament/Resources/ResourceContributorResource.php`
  - [x] Generate with: `php artisan make:filament-resource ResourceContributor --generate`
  - [x] Configure table columns:
    - [x] Logo (image column, circular)
    - [x] Name (searchable, sortable)
    - [x] Organization
    - [x] Active status (toggle column)
    - [x] Order
  - [x] Configure form schema:
    - [x] Section: "Basic Information"
      - [x] TextInput for `name` (required)
      - [x] TextInput for `organization`
      - [x] TextInput for `website_url` (url validation, prefix icon)
      - [x] Textarea for `description`
    - [x] Section: "Branding"
      - [x] FileUpload for `logo`
        - [x] Image only
        - [x] Max size: 2MB
        - [x] Disk: public
        - [x] Directory: 'contributor-logos'
        - [x] Image editor enabled
    - [x] Section: "Display Settings"
      - [x] Toggle for `is_active`
      - [x] TextInput for `order` (numeric, default 0)
    - [x] Section: "Associated Pages" (read-only)
      - [x] View list of pages using this contributor
  - [x] Configure filters:
    - [x] Active status (ternary)
  - [x] Configure actions:
    - [x] Edit, Delete
    - [x] Custom action: "View Pages" (lists all pages using contributor)

---

## Phase 4: WYSIWYG Editor Integration

### 4.1 FilamentPHP TipTap Editor
- [ ] Install FilamentTipTapEditor plugin
  - [ ] `composer require awcodes/filament-tiptap-editor`
  - [ ] Publish config: `php artisan vendor:publish --tag=filament-tiptap-editor-config`
- [ ] Configure TipTapEditor in Page form
  - [ ] Replace RichEditor with TiptapEditor::make('content')
  - [ ] Enable tools: Heading (H1-H6), Bold, Italic, Underline, Strike
  - [ ] Enable: BulletList, OrderedList, Blockquote, CodeBlock
  - [ ] Enable: Link, Image (with upload), Table
  - [ ] Enable: TextAlign, Color, Highlight
  - [ ] Configure media uploads:
    - [ ] Disk: 'public'
    - [ ] Directory: 'page-media'
    - [ ] Max file size: 5MB for images
- [ ] Add custom CSS for editor preview styles
- [ ] Test H2 extraction from editor content

### 4.2 Alternative: CKEditor (if preferred)
- [ ] Install FilamentCKEditor plugin (alternative to TipTap)
  - [ ] `composer require danielbehrendt/filament-ckeditor`
  - [ ] Similar configuration as above

---

## Phase 5: Sections/Anchors Functionality

### 5.1 H2 Extraction Service
- [ ] Create `app/Services/PageSectionExtractor.php`
  - [ ] Method: `extractSectionsFromHtml($html)`
    - [ ] Parse HTML content
    - [ ] Find all H2 tags
    - [ ] Extract text content
    - [ ] Generate URL-friendly anchors (slug format)
    - [ ] Return array of ['heading' => 'text', 'anchor' => 'slug']
  - [ ] Method: `injectAnchorIds($html)`
    - [ ] Parse HTML
    - [ ] Add `id="anchor-slug"` to each H2 tag
    - [ ] Return modified HTML
  - [ ] Handle duplicate headings (append -2, -3, etc.)

### 5.2 Page Model Integration
- [ ] Update Page model:
  - [ ] Add method `getTableOfContents()` using PageSectionExtractor
  - [ ] Add accessor `contentWithAnchors()` that injects anchor IDs
  - [ ] Cache TOC in model property to avoid re-parsing

### 5.3 Optional: Store Sections in Database
- [ ] Create observer `app/Observers/PageObserver.php`
  - [ ] On `saved` event: extract sections and update `page_sections` table
  - [ ] Clear old sections and insert new ones
  - [ ] This allows for custom anchor editing in admin

### 5.4 Admin Panel Section Preview
- [ ] Add custom Filament action to Page resource: "Preview Sections"
  - [ ] Extract sections from current content
  - [ ] Display in modal with heading and anchor
  - [ ] Allow copying anchors to clipboard
  - [ ] Show example: `#section-anchor`

---

## Phase 6: Media Management

### 6.1 Books Media Management
- [ ] Create custom Filament page: `app/Filament/Pages/BooksMediaManager.php`
  - [ ] Navigation: "Media" group, "Books (PDFs)"
  - [ ] Use FilamentPHP FileUpload or custom media browser
  - [ ] Show list of PDFs in storage/app/public/books
  - [ ] Features:
    - [ ] Upload new PDFs (drag & drop, multiple)
    - [ ] Delete PDFs (with confirmation)
    - [ ] View file details (size, upload date, dimensions)
    - [ ] Search/filter by filename
    - [ ] Bulk actions: delete, move
    - [ ] Show which books reference each PDF
    - [ ] Validate: PDF only, max size

### 6.2 Page Media Management
- [ ] Create custom Filament page: `app/Filament/Pages/PageMediaManager.php`
  - [ ] Navigation: "Media" group, "Page Assets"
  - [ ] Show list of files in storage/app/public/page-media
  - [ ] Features:
    - [ ] Upload images/files (images, documents)
    - [ ] Organize by folders/categories
    - [ ] Image preview thumbnails
    - [ ] Copy URL to clipboard for embedding
    - [ ] Delete files (with usage check)
    - [ ] Search/filter
    - [ ] Show which pages use each image
    - [ ] Validate: images (jpg, png, gif, svg), docs (pdf, doc)

### 6.3 Alternative: Use FilamentPHP Curator Plugin
- [ ] Option A: Install Curator plugin for advanced media library
  - [ ] `composer require awcodes/filament-curator`
  - [ ] Provides built-in media management
  - [ ] Supports folders, tagging, search
  - [ ] Integrates with TipTap editor
- [ ] Configure for books and page media

### 6.4 Storage Configuration
- [ ] Ensure `storage/app/public` is symlinked: `php artisan storage:link`
- [ ] Update `config/filesystems.php` if needed
- [ ] Configure disk for books: 'books' => storage_path('app/public/books')
- [ ] Configure disk for page media: 'page-media' => storage_path('app/public/page-media')

---

## Phase 7: Frontend Routes & Controllers

### 7.1 Routes
- [ ] Update `routes/web.php`
  - [ ] Add route: `Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show')`
  - [ ] Alternatively: `Route::get('/{slug}', ...)` for root-level pages (check conflicts)
  - [ ] Add route for page previews (admin only): `Route::get('/admin/pages/{id}/preview', ...)`

### 7.2 Page Controller
- [ ] Create `app/Http/Controllers/PageController.php`
  - [ ] Method: `show($slug)`
    - [ ] Find page by slug
    - [ ] Check if published (or admin user)
    - [ ] Eager load resourceContributors relationship
    - [ ] Extract table of contents from content
    - [ ] Inject anchor IDs into content HTML
    - [ ] Return view with page data
    - [ ] Handle 404 if not found

---

## Phase 8: Frontend Views & Components

### 8.1 Page Layout
- [ ] Create `resources/views/pages/show.blade.php`
  - [ ] Extend main layout
  - [ ] Display page title as H1
  - [ ] Show publication date
  - [ ] Sidebar or top section: Table of Contents
    - [ ] List all H2 sections as links
    - [ ] Smooth scroll to anchors
    - [ ] Highlight current section on scroll (optional, JS)
  - [ ] Main content area: render `{!! $page->contentWithAnchors !!}`
  - [ ] Bottom section: Resource Contributors
    - [ ] Display contributor logos and info
    - [ ] Grid layout with links to websites
  - [ ] Add meta tags for SEO (description, keywords)

### 8.2 Table of Contents Component
- [ ] Create `resources/views/components/page-toc.blade.php`
  - [ ] Accept sections array as prop
  - [ ] Render as nested list
  - [ ] Add Alpine.js for sticky TOC behavior
  - [ ] Add active section highlighting (optional)

### 8.3 Resource Contributors Component
- [ ] Create `resources/views/components/resource-contributors.blade.php`
  - [ ] Accept contributors collection as prop
  - [ ] Display in grid with logos
  - [ ] Show name, organization, description
  - [ ] Link to website if available
  - [ ] Responsive layout

### 8.4 Styling
- [ ] Style page content with Tailwind CSS
  - [ ] Ensure H2 anchors are styled consistently
  - [ ] Add spacing between sections
  - [ ] Style TOC sidebar (sticky, bordered)
  - [ ] Style contributor cards
  - [ ] Ensure responsive design (mobile TOC as collapsible)

---

## Phase 9: Navigation & Menu Integration

### 9.1 Add Pages to Site Navigation
- [ ] Update main navigation menu to include CMS pages
  - [ ] Option A: Manually add links in navigation component
  - [ ] Option B: Create dynamic menu from published pages
  - [ ] Add `order` field to control menu positioning
  - [ ] Support parent-child page hierarchy in menu

### 9.2 Menu Builder (Optional)
- [ ] Consider using FilamentPHP Navigation plugin for drag-drop menu builder
  - [ ] Allows admins to customize menu structure
  - [ ] Can include both CMS pages and custom links

---

## Phase 10: Testing & Refinement

### 10.1 Admin Panel Testing
- [ ] Test page creation with all fields
- [ ] Test WYSIWYG editor (formatting, images, links)
- [ ] Test slug generation and uniqueness validation
- [ ] Test resource contributor creation and assignment
- [ ] Test media upload for logos and page images
- [ ] Test section extraction preview
- [ ] Test page publish/unpublish toggle

### 10.2 Frontend Testing
- [ ] Test page rendering with various content
- [ ] Test table of contents links and scrolling
- [ ] Test anchor navigation (direct URL with #anchor)
- [ ] Test resource contributors display
- [ ] Test responsive design (mobile, tablet, desktop)
- [ ] Test SEO meta tags in page source

### 10.3 Edge Cases
- [ ] Page with no H2 sections (no TOC)
- [ ] Page with duplicate H2 headings (unique anchors)
- [ ] Page with special characters in headings (anchor generation)
- [ ] Unpublished page access attempt
- [ ] Missing contributor logo (fallback image)
- [ ] Very long page content (TOC scroll behavior)

### 10.4 Performance
- [ ] Add eager loading for relationships in queries
- [ ] Cache rendered page content if needed
- [ ] Optimize image uploads (compression, WebP)
- [ ] Test with large HTML content in editor

---

## Phase 11: Optional Enhancements

### 11.1 Page Templates
- [ ] Add `template` field to pages table
- [ ] Create different Blade templates (full-width, sidebar, landing)
- [ ] Allow admin to select template in form

### 11.2 Page Versioning
- [ ] Add version history for pages
- [ ] Allow reverting to previous versions
- [ ] Show who made changes and when

### 11.3 Search Integration
- [ ] Add CMS pages to site search results
- [ ] Index page content for full-text search

### 11.4 Custom Fields
- [ ] Add support for custom fields per page (key-value pairs)
- [ ] Useful for page-specific metadata or features

### 11.5 Breadcrumbs
- [ ] Generate breadcrumb navigation from page hierarchy
- [ ] Display at top of page

---

## Database Schema Summary

```sql
-- Pages
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    meta_description TEXT,
    meta_keywords TEXT,
    is_published BOOLEAN DEFAULT 1,
    published_at TIMESTAMP,
    order INT DEFAULT 0,
    parent_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
);

-- Resource Contributors
CREATE TABLE resource_contributors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    organization VARCHAR(255),
    logo VARCHAR(255),
    website_url VARCHAR(255),
    description TEXT,
    order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Pivot: Pages <-> Contributors
CREATE TABLE page_resource_contributor (
    page_id BIGINT UNSIGNED,
    resource_contributor_id BIGINT UNSIGNED,
    order INT DEFAULT 0,
    PRIMARY KEY (page_id, resource_contributor_id),
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_contributor_id) REFERENCES resource_contributors(id) ON DELETE CASCADE
);

-- Optional: Page Sections
CREATE TABLE page_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED,
    heading VARCHAR(255) NOT NULL,
    anchor VARCHAR(255) NOT NULL,
    order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
);
```

---

## Progress Tracking

- [x] Phase 1: Database Design & Migrations (4/4 complete) ✅
- [x] Phase 2: Models & Relationships (3/3 complete) ✅
- [x] Phase 3: FilamentPHP Admin Resources (3/3 complete) ✅
- [ ] Phase 4: WYSIWYG Editor Integration (0/2 complete)
- [ ] Phase 5: Sections/Anchors Functionality (0/4 complete)
- [ ] Phase 6: Media Management (0/4 complete)
- [ ] Phase 7: Frontend Routes & Controllers (0/2 complete)
- [ ] Phase 8: Frontend Views & Components (0/4 complete)
- [ ] Phase 9: Navigation & Menu Integration (0/2 complete)
- [ ] Phase 10: Testing & Refinement (0/4 complete)
- [ ] Phase 11: Optional Enhancements (0/5 complete)

**Overall Progress: 27%** (3/11 phases complete)

---

## Notes & Decisions

### Architecture Decisions
- **No Role System**: All FilamentPHP admin users have full CMS access
- **Slug-based URLs**: Pages accessed via `/pages/{slug}` or `/{slug}`
- **H2 as Anchors**: Only H2 tags create TOC entries (can extend to H3 if needed)
- **Separate Contributors**: Contributors can be reused across multiple pages
- **Media Separation**: Books PDFs separate from page images/assets

### Plugin Recommendations
- **WYSIWYG**: FilamentTipTapEditor (modern, well-maintained)
- **Media**: FilamentCurator (optional, for advanced media library)
- **Navigation**: Custom implementation or third-party menu builder

### Performance Considerations
- Use eager loading for relationships
- Cache page content with sections if database approach used
- Optimize images on upload

### Security Considerations
- Validate file uploads (type, size)
- Sanitize HTML from WYSIWYG (or trust admin users)
- Prevent slug conflicts with existing routes

---

## Quick Start Commands

```bash
# Create migrations
php artisan make:migration create_pages_table
php artisan make:migration create_resource_contributors_table
php artisan make:migration create_page_resource_contributor_table
php artisan make:migration create_page_sections_table

# Create models
php artisan make:model Page
php artisan make:model ResourceContributor
php artisan make:model PageSection

# Create Filament resources
php artisan make:filament-resource Page --generate
php artisan make:filament-resource ResourceContributor --generate

# Create controller
php artisan make:controller PageController

# Create service
php artisan make:class Services/PageSectionExtractor

# Install TipTap Editor
composer require awcodes/filament-tiptap-editor
php artisan vendor:publish --tag=filament-tiptap-editor-config

# Optional: Install Curator for media
composer require awcodes/filament-curator
php artisan curator:install

# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## Timeline Estimate

- **Phase 1-2** (Database & Models): 2-3 hours
- **Phase 3** (Admin Resources): 3-4 hours
- **Phase 4** (WYSIWYG Setup): 1-2 hours
- **Phase 5** (Sections/Anchors): 2-3 hours
- **Phase 6** (Media Management): 3-4 hours
- **Phase 7-8** (Frontend): 3-4 hours
- **Phase 9** (Navigation): 1-2 hours
- **Phase 10** (Testing): 2-3 hours

**Total Estimated Time: 17-25 hours**

---

*Last Updated: 2025-10-29*
