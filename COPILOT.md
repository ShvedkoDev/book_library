# COPILOT.md - Project Context for AI Sessions

**Last Updated:** 2025-12-03  
**Project:** FSM National Vernacular Language Arts (VLA) Curriculum

---

## 🎯 Project Overview

A comprehensive digital library web application for teachers in Micronesia, housing ~2,000 educational books in local languages with advanced search, filtering, and management capabilities.

### Key Statistics
- **Total Books in CSV:** 42 records (main-master-table.csv)
- **Database Models:** 38 models
- **Filament Resources:** 111 resource files
- **Controllers:** 19 controllers
- **Migrations:** 50+ database migrations

---

## 🏗️ Architecture & Tech Stack

### Backend
- **Framework:** Laravel 12.x
- **PHP Version:** 8.2
- **Database:** MySQL 8.0
- **Admin Panel:** FilamentPHP 3.3+ (awcodes/filament-tiptap-editor: ^3.5)
- **Authentication:** Laravel Breeze 2.3.8 (Blade views)
- **API:** Laravel Sanctum 4.0

### Frontend
- **Framework:** Livewire 3.6+ (no dedicated Livewire components found yet)
- **JavaScript:** Alpine.js 3.4.2 (via Livewire)
- **Styling:** Tailwind CSS 3.1+ with @tailwindcss/forms
- **Build Tool:** Vite 7.0.4
- **UI Libraries:** Tabulator Tables 6.3.1, XLSX 0.18.5

### Development Tools
- **Code Quality:** Laravel Pint 1.24
- **Testing:** PHPUnit 11.5.3
- **Process Management:** Concurrently 9.0.1
- **Logging:** Laravel Pail 1.2.2

---

## 🐳 Docker Environment

### Services
```yaml
app:       PHP 8.2-FPM container (ports: 8899:8000, 5173:5173, 5174:5174)
db:        MySQL 8.0 (port: 3307 → 3306)
nginx:     Nginx Alpine (port: 80:80)
phpmyadmin: PHPMyAdmin (port: 8080:80)
```

### Access Points
- **Main Application:** http://localhost
- **Admin Panel:** http://localhost/admin
- **PHPMyAdmin:** http://localhost:8080
- **Database (from host):** localhost:3307
- **Dev Server:** localhost:8899

### Database Credentials
```
DB_CONNECTION=mysql
DB_HOST=db (inside container) / localhost (from host)
DB_PORT=3306 (inside) / 3307 (from host)
DB_DATABASE=book_library
DB_USERNAME=root
DB_PASSWORD=secret
```

---

## 📊 Database Structure

### Core Book Models (38 Total)

#### Primary Entities
- **Book** - Main book entity with 42+ fillable fields
- **User** - Authentication and user management
- **Creator** - Authors/contributors
- **Publisher** - Publishing organizations
- **Collection** - Book collections/series
- **Language** - Languages (with ISO codes)
- **GeographicLocation** - Geographic coverage

#### Classification System
- **ClassificationType** - Classification schemas (Purpose, Grade Level, etc.)
- **ClassificationValue** - Individual classification values
- **BookClassification** - Pivot for book classifications

#### Book Relationships
- **BookCreator** - Pivot for book-creator relationships
- **BookLanguage** - Pivot for book languages
- **BookLocation** - Pivot for geographic locations
- **BookRelationship** - Book-to-book relationships (translated, revised, etc.)
- **BookKeyword** - Book keywords/tags
- **BookIdentifier** - ISBNs, DOIs, etc.

#### Files & Media
- **BookFile** - PDF files and other attachments
- **FileRecord** - File metadata tracking

#### User Interactions
- **BookView** - Page view tracking
- **BookDownload** - Download tracking
- **BookRating** - User ratings
- **BookReview** - User reviews
- **BookBookmark** - Book favorites (deprecated?)
- **UserBookmark** - User bookmarks (new)
- **BookNote** - User notes on books
- **BookShare** - Social sharing tracking

#### Analytics
- **SearchQuery** - Search term tracking
- **FilterAnalytic** - Filter usage analytics

#### CMS & Settings
- **Page** - Dynamic CMS pages (Guide section)
- **PageSection** - Page content sections
- **ResourceContributor** - Project contributors
- **TermsOfUseVersion** - Terms of use versions
- **UserTermsAcceptance** - User acceptance tracking
- **Setting** - Application settings

#### Import/Export
- **CsvImport** - CSV import job tracking
- **DataQualityIssue** - Data validation issues

#### Access Control
- **AccessRequest** - User access requests
- **LibraryReference** - External library links

---

## 🔀 Key Routes (web.php)

### Public Routes
- `GET /` - Homepage (CMS or welcome view)
- `GET /library` - Library browsing (public)
- `GET /library/book/{slug}` - Book detail page (public)
- `GET /{slug}` - CMS page catch-all

### Authenticated Routes
- `GET /dashboard` - User dashboard
- `GET /library/book/{book}/viewer/{file}` - PDF viewer
- `GET /library/book/{book}/view-pdf/{file}` - Direct PDF view
- `GET /library/book/{book}/download/{file}` - PDF download
- `POST /library/book/{book}/request-access` - Request access
- `POST /library/book/{book}/rate` - Submit rating
- `POST /library/book/{book}/review` - Submit review
- `POST /library/book/{book}/bookmark` - Toggle bookmark
- `GET /my-bookmarks` - User bookmarks
- `GET /my-activity/*` - User activity pages

### Profile Routes
- `GET /profile` - Edit profile
- `GET /my-activity/ratings` - User's ratings
- `GET /my-activity/reviews` - User's reviews
- `GET /my-activity/downloads` - Download history
- `GET /my-activity/bookmarks` - Bookmarks
- `GET /my-activity/notes` - User notes
- `GET /my-activity/timeline` - Activity timeline

### Admin Routes
- `/admin` - FilamentPHP admin panel
- `/admin/pages/{id}/preview` - CMS page preview
- `/admin/users/{user}/activity` - View user activity
- `/csv/download-template/{type}` - CSV templates
- `/csv/download-export/{filename}` - CSV exports
- `/admin/media/download/{file}` - Media downloads

### API Routes
- `POST /api/track-share` - Track social shares (no auth)

---

## 🎨 UI Templates

### Location: `/public/ui-test/final/`

### Available Templates
1. **index.html** - Guide homepage (49 KB)
2. **library.html** - Library catalog page (61 KB)
3. **book.html** - Book detail page (110 KB)
4. **login.html** - Login page (12 KB)
5. **uikit.html** - Complete UI component library (67 KB)

### Features Implemented
- ✅ Responsive design (mobile + desktop)
- ✅ Real-time search functionality
- ✅ Multi-category filters (Subject, Grade, Type, Language, Year)
- ✅ Pagination (10 items per page)
- ✅ Book detail pages with ratings/reviews
- ✅ Access level indicators
- ✅ Related content sections
- ✅ Multiple editions support
- ✅ User interaction UI (favorites, share, rate)

### Design System
- **Typography:** Proxima Nova (sans-serif)
- **Colors:** Institutional palette (greens, blues, grays)
- **Framework:** WordPress CSS variables
- **Components:** Extracted in uikit.html

---

## 📁 Key Directories

### Application Structure
```
app/
├── Console/Commands/        # 11 artisan commands
│   ├── ImportBooksFromCsv.php
│   ├── ExportBooksToCsv.php
│   ├── DatabaseBackup.php
│   ├── FixBookFilePaths.php
│   └── VerifyDataQuality.php
├── Filament/               # 111+ admin resources
│   ├── Resources/
│   ├── Widgets/
│   └── Pages/
├── Http/Controllers/       # 19 controllers
│   ├── LibraryController.php (main library logic)
│   ├── PageController.php (CMS)
│   ├── BookmarkController.php
│   ├── BookNoteController.php
│   └── UserProfileController.php
├── Models/                 # 38 Eloquent models
├── Services/               # Business logic services
│   ├── AnalyticsService.php
│   ├── BookCsvImportService.php
│   ├── BookCsvExportService.php
│   ├── BookDuplicationService.php
│   ├── DataQualityService.php
│   ├── DatabaseBackupService.php
│   └── ThumbnailService.php
├── Policies/
│   └── BookPolicy.php
└── Jobs/
    └── ImportBooksFromCsvJob.php
```

### Frontend Structure
```
resources/
├── views/
│   ├── auth/              # Breeze authentication views
│   ├── library/           # Library catalog views
│   ├── pages/             # CMS page views
│   ├── profile/           # User profile views
│   ├── bookmarks/         # Bookmark management
│   ├── components/        # Blade components
│   ├── layouts/           # Layout templates
│   └── filament/          # FilamentPHP customizations
├── css/
└── js/
```

### Database
```
database/
├── migrations/            # 50+ migration files
├── seeders/              # 15 seeder classes
│   ├── BookSeeder.php (31KB - comprehensive)
│   ├── ClassificationTypeSeeder.php
│   ├── ClassificationValueSeeder.php
│   ├── CreatorSeeder.php
│   ├── LanguageSeeder.php
│   └── UserSeeder.php
└── factories/
```

### Storage & Public
```
public/
├── ui-test/final/        # UI templates (ready for integration)
└── storage/              # Symlink to storage/app/public

storage/
├── app/public/           # Public file storage
├── csv-templates/        # Import/export templates
├── csv-exports/          # Generated exports
└── logs/
```

---

## 🔧 Key Services

### AnalyticsService
Centralized tracking service for:
- Book views (with IP, user agent)
- Book downloads
- Search queries (including zero-result tracking)
- Filter usage by type

### BookCsvImportService
Handles CSV imports with:
- Validation
- Relationship creation
- Error tracking
- Performance metrics
- Data quality checks

### BookCsvExportService
Exports books to CSV with all relationships.

### BookDuplicationService
Creates book copies with proper relationship handling.

### DataQualityService
Validates data integrity and tracks issues.

### DatabaseBackupService
Manages database backups and restoration.

### ThumbnailService
Generates and manages book cover thumbnails.

---

## 📈 Analytics Features

### Admin Dashboard Widgets
- **30-Day Summary**: Views, downloads, searches, unique books

### Analytics Resources
1. **Book Views** (`/admin/book-views`)
   - Grouped by book with total counts
   - Detailed breakdown per book
   - IP tracking, user agents
   - Time filters (24h, 7d, 30d)

2. **Book Downloads** (`/admin/book-downloads`)
   - Similar to views with download-specific data
   - User tracking
   - Download history

3. **Search Queries** (`/admin/search-queries`)
   - All search terms with result counts
   - Zero-result tracking (red badge)
   - Popular query identification
   - Time-based filters

4. **Filter Analytics** (`/admin/filter-analytics`)
   - Usage by filter type (Subject, Grade, Language, Type, Year)
   - Usage counts per filter value
   - Time-based analysis

---

## 📝 Book Model Key Fields

### Basic Information
```php
'internal_id'          // Internal tracking ID
'palm_code'            // PALM system code
'title'                // Main title
'subtitle'             // Subtitle
'translated_title'     // Translation
'slug'                 // URL-friendly identifier (auto-generated)
```

### Publication Details
```php
'physical_type'        // Book type
'collection_id'        // FK to collections
'publisher_id'         // FK to publishers
'publication_year'     // Year published
'pages'                // Page count
```

### Content
```php
'description'          // Full description
'abstract'             // Brief abstract (NEW)
'toc'                  // Table of contents
'notes_issue'          // Issue notes
'notes_version'        // Version notes (NEW)
'notes_content'        // Content notes
'contact'              // Contact information
```

### Access & Standards
```php
'access_level'         // Full/Limited/Unavailable
'vla_standard'         // VLA standard
'vla_benchmark'        // VLA benchmark
```

### Status & Metrics
```php
'is_featured'          // Featured flag
'is_active'            // Active status
'view_count'           // Total views
'download_count'       // Total downloads
'sort_order'           // Manual sorting
```

### Duplication Tracking
```php
'duplicated_from_book_id'  // Source book for duplicates
'duplicated_at'            // When duplicated
```

---

## 🔐 Access Control

### User Roles (via FilamentPHP)
- **Admin** - Full system access
- **Regular User** - Library browsing, personal features

### Book Access Levels
1. **Full Access** - View, download, all features
2. **Limited Access** - View only, download restricted
3. **Unavailable** - Visible but not accessible

### Access Request System
Users can request access to restricted books via `AccessRequest` model.

---

## 🚀 Deployment Scripts

### Available Scripts
```bash
build-and-commit.sh      # Build assets and commit
clear-caches.sh          # Clear all Laravel caches
deploy-assets.sh         # Deploy frontend assets
deploy-production.sh     # Full production deployment
fix-permissions.sh       # Fix file permissions
prepare-deployment.sh    # Pre-deployment tasks
update-production.sh     # Update production environment
update-server.sh         # Server update script
update-simple.sh         # Simple update process
```

### Docker Commands
```bash
docker-compose up -d              # Start services
docker-compose down               # Stop services
docker-compose logs -f            # View logs
docker-compose exec app bash     # Access container
docker-compose exec app php artisan migrate
```

---

## 🧪 Testing & Quality

### Test Structure
```
tests/
├── Feature/
└── Unit/
```

### Running Tests
```bash
# Inside container
php artisan test

# Via composer script
composer test
```

### Code Quality
```bash
# Laravel Pint (code formatting)
./vendor/bin/pint
```

---

## 📦 CSV Import System

### Import Features
- Comprehensive validation
- Relationship mapping (creators, languages, classifications)
- Error tracking via DataQualityIssue model
- Performance metrics
- Batch processing via queue jobs
- Rollback capability

### CSV Structure (42 records in main-master-table.csv)
Fields include book metadata, classifications, creators, languages, etc.

### Import Commands
```bash
php artisan import:books-from-csv {file}
php artisan export:books-to-csv
php artisan check:import-prerequisites
php artisan reset:book-data
```

---

## 🎨 CMS System

### Page Management
- Dynamic page creation via FilamentPHP
- TipTap rich text editor
- Page sections with ordering
- Resource contributors linking
- Homepage designation
- Navigation visibility toggle
- Slug-based routing
- Preview functionality
- Soft deletes

### CMS Routes
- `GET /{slug}` - View published page
- `GET /admin/pages/{id}/preview` - Preview (admin only)

---

## 📚 Key Relationships

### Book Relationships
```php
Book hasMany BookFile
Book hasMany BookView
Book hasMany BookDownload
Book belongsTo Publisher
Book belongsTo Collection
Book belongsToMany Creator (via BookCreator)
Book belongsToMany Language (via BookLanguage)
Book belongsToMany ClassificationValue (via BookClassification)
Book belongsToMany GeographicLocation (via BookLocation)
Book hasMany BookRelationship (self-referencing)
Book hasMany BookRating
Book hasMany BookReview
Book hasMany BookKeyword
Book hasMany BookIdentifier
```

### User Relationships
```php
User hasMany BookView
User hasMany BookDownload
User hasMany BookRating
User hasMany BookReview
User hasMany UserBookmark
User hasMany BookNote
User hasMany SearchQuery
User hasMany AccessRequest
```

---

## 🔍 Search & Filter System

### Search Capabilities
- Real-time keyword search (titles, descriptions)
- Multi-category filters with AND logic
- Pagination (10 items per page)
- Sort options
- Zero-result tracking

### Filter Categories
1. **Purpose** (Subject/Classification)
2. **Grade Level** (Learner level)
3. **Language**
4. **Resource Type**
5. **Publication Year**

### Filter Analytics
All filter usage tracked in `FilterAnalytic` model for optimization.

---

## 🎯 Current Status

### ✅ Completed
- Database schema (50+ migrations)
- Admin panel (FilamentPHP with 111+ resources)
- Analytics system (views, downloads, searches, filters)
- User authentication (Breeze)
- UI templates (Guide + Library)
- Book management
- CSV import/export system
- CMS for Guide pages
- User interaction features (bookmarks, notes, reviews)
- Access control system
- Duplication system

### 🚧 Integration Needed
- Convert HTML templates to Livewire components
- Integrate UI templates with backend
- Connect search/filter UI to LibraryController
- Test full user journey end-to-end
- Import remaining books from CSV

### 📋 Future Enhancements
- Language selector implementation
- Enhanced PDF viewer
- Advanced search options
- Recommendation engine
- Email notifications
- API for mobile apps

---

## 🐛 Known Issues & Fixes

### Documentation Files
- `CSV_IMPORT_FINAL_FIXES.md` - Import system fixes
- `CSV_IMPORT_FIXES_SUMMARY.md` - Summary of import fixes
- `DATABASE_UPDATE_SUMMARY.md` - Database changes log
- `IMPLEMENTATION_COMPLETE_SUMMARY.md` - Feature completion notes
- `SEEDER_UPDATE_SUMMARY.md` - Seeder updates

### Recent Fixes
- Book file path corrections
- Relationship type additions (translated)
- Library reference enhancements
- Book identifier system
- Abstract and notes separation

---

## 🔨 Common Development Tasks

### Artisan Commands
```bash
# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# FilamentPHP
php artisan filament:upgrade
php artisan make:filament-resource Book

# Import/Export
php artisan import:books-from-csv main-master-table.csv
php artisan export:books-to-csv

# Backup
php artisan database:backup
php artisan database:restore {filename}

# Data Quality
php artisan verify:data-quality
```

### Vite Commands
```bash
npm run dev    # Development server
npm run build  # Production build
```

### Composer Scripts
```bash
composer dev   # Start dev environment (concurrent services)
composer test  # Run tests
```

---

## 📖 Reference Materials

### Design Inspiration
- Template: https://coe.hawaii.edu/stems2/ulu-education-toolkit-guide/
- Functionality: openlibrary.org

### Documentation
- Laravel 12.x: https://laravel.com/docs/12.x
- FilamentPHP 3.x: https://filamentphp.com/docs/3.x
- Livewire 3.x: https://livewire.laravel.com/docs/3.x
- Tailwind CSS: https://tailwindcss.com/docs

---

## 🎯 Project Goals

### Milestone 1 (10%) ✅
- Database setup
- 10 sample books
- Basic library search

### Milestone 2 (10%) ✅
- Complete design
- Full Guide module
- All 2,000 books imported (CSV ready with 42 records)

### Milestone 3 (10%) ✅
- User authentication
- Admin panel
- User functionality

### Milestone 4 (70%) 🚧
- Testing
- Optimization
- Deployment
- Integration of UI templates

---

## 💡 Development Notes

### Important Conventions
1. **Slugs**: Auto-generated from titles on book creation
2. **Access Levels**: Enum-like strings (not database-enforced)
3. **File Storage**: `storage/app/public` symlinked to `public/storage`
4. **PDF Paths**: Stored in `BookFile` model
5. **Thumbnails**: Generated via ThumbnailService
6. **Queue Jobs**: Database driver for background processing

### Performance Considerations
- Views and downloads indexed for fast analytics
- Search queries cached where appropriate
- Pagination standard: 10 items per page
- CSV exports expire after 24 hours

### Security Notes
- Book access controlled via `BookPolicy`
- Admin routes protected by FilamentPHP auth
- PDF access requires authentication
- Share tracking endpoint is public (no auth)
- XSS protection via Laravel's Blade escaping

---

## 🔗 External Integrations

### Planned (Not Yet Implemented)
- Cloud storage for PDFs (AWS S3)
- Email service for notifications
- Analytics service (Google Analytics)
- CDN for static assets

---

## 📞 Quick Reference

### Start Development
```bash
docker-compose up -d
docker-compose exec app bash
php artisan serve
npm run dev
```

### Access URLs
- App: http://localhost
- Admin: http://localhost/admin
- DB: localhost:3307 (user: root, pass: secret)

### Stop Development
```bash
docker-compose down
```

---

**End of COPILOT.md**

*This file contains essential context for AI assistants working on the FSM National Vernacular Language Arts (VLA) Curriculum project. Update as project evolves.*
