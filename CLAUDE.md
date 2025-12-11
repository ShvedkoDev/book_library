# FSM National Vernacular Language Arts (VLA) Curriculum

## Project Overview
Building a digital library website for teachers in Micronesia with ~2,000 educational books in local languages.

## Architecture
The website consists of two main modules:

### 1. The Guide (Static Content)
- Welcome hub with project information
- Landing page with sections navigation
- Content pages explaining how to use resources
- Partner information
- Clear entry point to Library

### 2. The Library (Dynamic Content) 
- Searchable catalog of ~2,000 books
- Advanced filtering by categories
- Book detail pages with metadata
- PDF viewing and download capabilities
- User authentication and reviews
- Admin panel for content management

## Technical Stack (Confirmed & Implemented)
- **Backend Framework**: Laravel 12.x ✅
- **Frontend**: Livewire 3.6.4 (with Alpine JS if needed) ✅
- **Styling**: Tailwind CSS ✅
- **Languages**: PHP 8.2, JavaScript, HTML, CSS ✅
- **Database**: MySQL 8.0 ✅
- **Admin Panel**: FilamentPHP 3.3.37 ✅
- **Authentication**: Laravel Breeze 2.3.8 ✅
- **Version Control**: Git + GitHub ✅
- **Containerization**: Docker ✅
- **Storage**: Server folders or cloud storage for PDFs

## Setup Progress ✅

### Environment Setup (Completed)
- ✅ **Laravel Project**: Fresh Laravel 12.x installation
- ✅ **Docker Environment**: Multi-service setup with app, database, nginx, phpmyadmin
- ✅ **Database**: MySQL 8.0 container running on port 3307
- ✅ **Authentication**: Laravel Breeze installed with Blade views
- ✅ **Admin Dashboard**: FilamentPHP installed and configured
- ✅ **Frontend**: Livewire + Tailwind CSS configured
- ✅ **Environment**: Database connection configured

### Current Infrastructure
- **App Container**: PHP 8.2-FPM with all required extensions
- **Database Container**: MySQL 8.0 (accessible on localhost:3307)
- **Web Server**: Nginx proxy (accessible on localhost:80)
- **Admin Interface**: PHPMyAdmin (accessible on localhost:8080)
- **Admin Panel**: FilamentPHP (accessible via /admin route)

### Packages Installed
```bash
laravel/breeze: ^2.3        # Authentication scaffolding
filament/filament: ^3.3     # Admin dashboard
livewire/livewire: ^3.6     # Dynamic frontend components
```

## UI Template Development ✅

### Frontend Templates (Completed)
- ✅ **Academic Template**: Clean academic design with proper pagination
- ✅ **Material Template**: Material Design with working search and filters
- ✅ **Modern Template**: Glass-morphism design with functional pagination
- ✅ **Final Template**: Finalized design with Guide and Library pages
- ✅ **Book Detail Page**: Individual book page template with full functionality
- ✅ **UI Kit**: Comprehensive design system with all components extracted from templates
- ✅ **Pagination System**: Fully functional pagination across all templates
- ✅ **Search Integration**: Real-time search with pagination support
- ✅ **Filter System**: Category filters with AND logic

### UI Template Features Implemented
- **Responsive Design**: All templates work on mobile and desktop
- **Search Functionality**: Real-time keyword search in titles and descriptions
- **Advanced Filtering**: Multi-category filters (Subject, Grade, Type, Language, Year)
- **Pagination Logic**: 10 items per page with proper navigation
- **State Management**: Search and filters integrate seamlessly with pagination
- **Visual Consistency**: Each template maintains its design identity
- **Accessibility**: Proper color contrast and navigation
- **Book Detail Pages**: Complete book template with metadata, ratings, reviews, and related content
- **Access Control Display**: Visual indicators for Full Access, Limited Access, and Unavailable books
- **User Interaction**: Star ratings, user actions (favorites, share, rate), and community features
- **Multiple Editions**: Support for displaying different versions of the same book
- **Related Content**: Three sections for same collection, same language, and other languages
- **UI Design System**: Complete ui-kit.html with all extracted components from templates
- **Component Library**: Typography, buttons, forms, navigation, cards, ratings organized by category
- **WordPress Integration**: All components use WordPress CSS variables and styling standards

### Templates Location
```
/public/ui-test/
├── academic/
│   ├── index.html
│   └── library.html     # ✅ Functional pagination
├── material/
│   ├── index.html
│   └── library.html     # ✅ Material Design with pagination
├── modern/
│   ├── index.html
│   └── library.html       # ✅ Glass design with fixed color contrast
└── final/
    ├── index.html         # ✅ Guide page template
    ├── library.html       # ✅ Library page template  
    ├── book.html          # ✅ Book detail page template
    ├── login.html         # ✅ Login page template
    └── ui-kit.html        # ✅ Complete UI design system
```

### Next Development Steps
1. **Database Design**: Create models and migrations for books, categories, authors
2. **Admin Resources**: Set up FilamentPHP resources for book management
3. **Laravel Integration**: Convert UI templates to Livewire components
4. **File Storage**: Configure PDF storage and serving
5. **User Roles**: Implement normal vs admin user permissions
6. **Data Import**: Import 2,000+ book records from Excel

### Docker Commands
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Access app container
docker-compose exec app bash
```

### Access Points
- **Main App**: http://localhost
- **Admin Panel**: http://localhost/admin
- **PHPMyAdmin**: http://localhost:8080
- **Database**: localhost:3307 (from host)

## Key Features

### Core & Shared Functionality
- Persistent top bar with logos
- Universal menu bar (changes between Guide/Library)
- User authentication (normal users vs admins)
- Language selector (placeholder for future)
- Terms of use modal
- Universal footer

### Guide Module Features
- Landing page with sections navigation
- Library entry buttons
- Resource contributors list
- Content page templates

### Library Module Features
- **Search & Filter**: Real-time keyword search, checkbox filters with AND logic
- **Results Display**: Grid layout with thumbnails, sorting options, pagination
- **Book Pages**: Dynamic pages with cover, metadata, ratings, reviews
- **Access Control**: View/Download buttons based on access level
- **Related Content**: Same language, other languages, same author, same collection
- **Multiple Editions**: Linked edition navigation
- **Analytics & Tracking**: Comprehensive tracking of views, downloads, searches, and filter usage

## Development Milestones
1. **Milestone 1 (10%)**: Database setup, 10 sample books, basic Library search
2. **Milestone 2 (10%)**: Complete design, full Guide module, all 2,000 books imported
3. **Milestone 3 (10%)**: User auth, admin panel, user functionality
4. **Milestone 4 (70%)**: Testing, optimization, deployment

## Data Structure
- Import from Excel spreadsheet with ~2,000 book records
- Multiple access levels: Full access, Limited access, Unavailable
- User ratings and community reviews
- Multiple editions linking

## Reference Materials
- Template design: https://coe.hawaii.edu/stems2/ulu-education-toolkit-guide/
- Project plan: LIBRARY-PLAN.pdf (needs to be provided)
- Inspiration: openlibrary.org functionality

## Template Analysis (Ulu Education Toolkit)
**Navigation Structure:**
- Hierarchical multi-level menu with dropdowns
- Clear categorization and institutional branding
- Prominent top-level navigation

**Design Elements:**
- Clean, minimalist design with ample white space
- Decorative horizontal dividers between sections
- Responsive design for multiple screen sizes
- Institutional color palette (greens, blues, grays)
- "proxima-nova" sans-serif typography
- Clear visual hierarchy with varied font weights

**UX Patterns:**
- Informative introduction with clear purpose
- Collaborative approach and partnership emphasis
- Transparent terms of use
- Multiple content entry points
- Educational accessibility focus

## User Journey
1. Land on Guide → Read about project → Accept terms
2. Enter Library → Search/filter books → View details → Download/view PDFs
3. Registered users can rate and review books
4. Admins can manage book database through admin panel

## Analytics & Tracking System ✅

### Overview
Comprehensive analytics system tracking user behavior and content performance across the library.

### Tracking Capabilities
- **Book Views**: Every book page visit tracked with user, IP, timestamp
- **Downloads**: PDF downloads tracked with user, IP, user agent
- **Search Queries**: All searches recorded with result counts and zero-result tracking
- **Filter Usage**: Popular filters tracked by type (subjects, grades, languages, types, years)

### Admin Panel Analytics

#### Dashboard Widget (`/admin`)
- **30-Day Summary Card** showing:
  - Total book views
  - Total downloads
  - Total searches performed
  - Unique books viewed

#### Book Views (`/admin/book-views`)
- **Grouped Overview**: Each book shown once with total view count
- **Latest View & First View** timestamps
- **Clickable Titles**: Link to detailed breakdown
- **Time Filters**: Last 24 hours, 7 days, 30 days
- **Sorted by Popularity**: Most viewed books first

#### Book Views Details (`/admin/book-views/{bookId}/details`)
- **Book Summary Card**: Total views, downloads, publication year
- **Individual View Records**: Complete list of all view events
  - Timestamp with "X ago" format and full date
  - User (or "Guest" for anonymous)
  - IP address (searchable, toggleable)
  - User agent (hidden by default, toggleable)
- **View Book Page Action**: Opens book in library (new tab)

#### Book Downloads (`/admin/book-downloads`)
- **Grouped Overview**: Each book shown once with total download count
- **Latest Download & First Download** timestamps
- **Clickable Titles**: Link to detailed breakdown
- **Time Filters**: Last 24 hours, 7 days, 30 days
- **Sorted by Popularity**: Most downloaded books first

#### Book Downloads Details (`/admin/book-downloads/{bookId}/details`)
- **Book Summary Card**: Total downloads, views, publication year
- **Individual Download Records**: Complete list of all download events
  - Timestamp with "X ago" format and full date
  - User (or "Guest" for anonymous)
  - IP address (searchable, toggleable)
  - User agent (hidden by default, toggleable)
- **View Book Page Action**: Opens book in library (new tab)

#### Search Queries (`/admin/search-queries`)
- **All Search Terms**: Complete search history with result counts
- **Zero Results Tracking**: Red badge for searches with no results
- **Popular Queries**: Identify most common search terms
- **Time-based Filters**: Last 7 days, 30 days
- **User Tracking**: Links searches to users when authenticated

#### Filter Analytics (`/admin/filter-analytics`)
- **Filter Type Breakdown**: Usage stats by category
  - Subjects (Purpose classifications)
  - Grades (Learner levels)
  - Languages
  - Resource Types
  - Publication Years
- **Usage Counts**: How many times each filter was applied
- **Time-based Filters**: Last 7 days, 30 days

### Technical Implementation
- **AnalyticsService**: Centralized service for all tracking operations
- **Automatic Tracking**: Controllers use dependency injection for seamless tracking
- **Database Tables**:
  - `book_views` - Individual view records
  - `book_downloads` - Individual download records
  - `search_queries` - Search history with results
  - `filter_analytics` - Filter usage patterns
- **Performance**: Indexed queries for fast analytics retrieval
- **Privacy**: IP addresses and user agents stored for analytics purposes

### Admin Access
All analytics features accessible through FilamentPHP admin panel under "Analytics" navigation group.
## CMS File Management System ✅

### Overview
Comprehensive file upload and management system for CMS pages and general file storage.

### File Upload Resource (`/admin/file-uploads`)
Universal file manager for uploading any file type to `storage/app/uploads/`.

**Features:**
- ✅ **Universal Upload**: Accept ANY file type (no restrictions)
- ✅ **Large Files**: Support up to 100MB per file
- ✅ **Metadata Tracking**: Automatic detection of filename, MIME type, size
- ✅ **User Tracking**: Records who uploaded each file
- ✅ **Descriptions**: Optional file descriptions
- ✅ **Search & Filter**: Find files by name, type, or description
- ✅ **Download**: Download files with original names
- ✅ **Path Copy**: Get relative and absolute file paths
- ✅ **Bulk Operations**: Delete multiple files at once

**File List Columns:**
- File name (clickable to copy)
- MIME type (badge)
- File size (human-readable)
- Description
- Uploaded by (user)
- Upload date/time

**Actions Available:**
- Download with original filename
- Copy file paths (relative and absolute)
- Edit description or replace file
- Delete file (removes from storage and database)
- Bulk delete with confirmation

**Storage Location:** `storage/app/uploads/`

**Documentation:** See `FILE_UPLOADS.md` for complete usage guide

### Page Media Manager (`/admin/page-media-manager`)
Specialized media manager for CMS page assets (images and PDFs).

**Features:**
- ✅ **Original Filenames**: Preserves original file names (like TipTap editor)
- ✅ **Image Support**: JPG, PNG, GIF, WebP, SVG
- ✅ **PDF Support**: Upload PDF documents
- ✅ **Image Editor**: Built-in image editing capabilities
- ✅ **Preview Thumbnails**: Visual preview of uploaded images
- ✅ **Usage Tracking**: See which pages use each file
- ✅ **Bulk Upload**: Upload multiple files at once
- ✅ **URL Copying**: Copy file URLs to clipboard

**File Operations:**
- View file in new tab
- Copy public URL
- Show pages using file
- Download file
- Delete with usage warnings

**Storage Location:** `storage/app/public/page-media/`

**Usage Warnings:**
- System detects if file is used by pages
- Shows warning before deleting in-use files
- Displays page list using the file

### TipTap Editor Integration
CMS pages use TipTap WYSIWYG editor with media upload support:
- Inline image uploads
- Images stored in `storage/app/public/page-media/`
- Supports JPG, PNG, GIF, WebP, SVG
- Max file size: 5MB
- Image editor included

### Custom HTML Blocks
CMS pages support custom HTML blocks alongside TipTap content:
- Create reusable HTML blocks with IDs
- Reference in main content using `{{block-id}}` placeholders
- Preserves complex HTML structures (divs, special classes)
- Merged with TipTap content during rendering

**Example:**
1. Create custom block with ID: `block-1`
2. Add HTML: `<div class="special-block">Content</div>`
3. Reference in TipTap content: `{{block-1}}`
4. System replaces placeholder when rendering page

### CMS Navigation Structure
All CMS-related features grouped under "CMS" navigation:
1. **Pages** (sort: 1) - CMS page management
2. **Resource Contributors** (sort: 2) - Page contributors
3. **Page Assets** (sort: 3) - Media manager for page images/PDFs
4. **File Uploads** (sort: 4) - Universal file upload manager

### CMS Pages Backup/Import
Export and import CMS pages between environments:
- **Export Command**: `php artisan pages:export`
- **Import Command**: `php artisan pages:import`
- Preserves content, custom HTML blocks, relationships
- Documentation: `CMS_PAGES_BACKUP.md`

### Technical Details
**Models:**
- `FileUpload` - Universal file upload records
- `Page` - CMS page content
- `ResourceContributor` - Page contributors
- `FileRecord` - In-memory model for page media

**Database Tables:**
- `file_uploads` - Universal file metadata
- `pages` - CMS page content
- `page_resource_contributor` - Pivot table

**Services:**
- `PageSectionExtractor` - Extracts h2 sections for navigation
- Page rendering with placeholder replacement

**Storage Disks:**
- `local` disk for File Uploads (`storage/app/`)
- `public` disk for Page Media (`storage/app/public/`)
