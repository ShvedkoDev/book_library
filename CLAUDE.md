# Micronesian Teachers Digital Library Project

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
    ├── index.html
    └── book.html          # ✅ Book detail page template
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