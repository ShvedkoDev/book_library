# Micronesian Teachers Digital Library

A digital library website for teachers in Micronesia featuring approximately 2,000 educational books in local languages.

## Project Overview

This project builds a comprehensive digital library platform consisting of two main modules:

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

## Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Livewire 3.6.4 with Alpine.js
- **Styling**: Tailwind CSS
- **Database**: MySQL 8.0
- **Admin Panel**: FilamentPHP 3.3.37
- **Authentication**: Laravel Breeze 2.3.8
- **Containerization**: Docker
- **Languages**: PHP 8.2, JavaScript, HTML, CSS

## Getting Started

### Prerequisites
- Docker and Docker Compose installed on your system
- Git (for version control)

### Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd book_library
   ```

2. **Start the Docker environment**
   ```bash
   docker-compose up -d
   ```

3. **Check that all containers are running**
   ```bash
   docker-compose ps
   ```

### Access Points

Once the Docker environment is running, you can access:

- **Main Application**: [http://localhost](http://localhost)
- **Admin Panel**: [http://localhost/admin](http://localhost/admin)
- **PHPMyAdmin**: [http://localhost:8080](http://localhost:8080)
- **Database**: `localhost:3307` (from host machine)

### Default Admin Credentials

For accessing the admin panel at `/admin`:

```
Email: admin@micronesianlib.edu
Password: password123
```

**⚠️ Security Note**: These are default development credentials. Change them immediately in production environments.

### Docker Management

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down

# Access the app container
docker-compose exec app bash
```

## Features

### Core Functionality
- Persistent top bar with logos
- Universal menu bar (changes between Guide/Library)
- User authentication system
- Language selector (placeholder for future)
- Terms of use modal
- Universal footer

### Search & Library Features
- Real-time keyword search
- Advanced filtering by categories (Subject, Grade, Type, Language, Year)
- Grid layout with thumbnails
- Sorting options and pagination
- Book detail pages with ratings and reviews
- Access control (Full Access, Limited Access, Unavailable)
- Related content suggestions
- Multiple editions support

## Development

### Container Structure
- **App Container**: PHP 8.2-FPM with all required extensions
- **Database Container**: MySQL 8.0
- **Web Server**: Nginx proxy
- **Admin Interface**: PHPMyAdmin

### Installed Packages
- `laravel/breeze: ^2.3` - Authentication scaffolding
- `filament/filament: ^3.3` - Admin dashboard
- `livewire/livewire: ^3.6` - Dynamic frontend components

## Database

The application uses MySQL 8.0 with a comprehensive database structure including:
- Books catalog with metadata
- User authentication and roles
- CMS pages and content blocks
- Categories and filtering system
- User ratings and reviews

## Templates

UI templates are available in `/public/ui-test/` for development reference:
- Academic design template
- Material design template
- Modern glass-morphism template
- Final production template with complete UI kit

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).