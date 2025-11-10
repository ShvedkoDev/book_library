# Micronesian Teachers Digital Library

A digital library platform for teachers in Micronesia, providing access to ~2,000 educational books in local languages.

## 📚 Project Overview

This application consists of two main modules:

1. **The Guide** - Static content with project information and resources
2. **The Library** - Dynamic searchable catalog with book management and user features

**Live Site:** https://micronesian.school (when deployed)

## 🚀 Quick Links

### For Development
- **[CLAUDE.md](./CLAUDE.md)** - Complete project documentation and technical details
- **[TODO_REDESIGN.md](./TODO_REDESIGN.md)** - Current tasks and development roadmap

### For Deployment
- **[DEPLOYMENT_README.md](./DEPLOYMENT_README.md)** - Main deployment guide (start here for first deployment)
- **[DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md)** - Quick deployment checklist
- **[PRODUCTION_DEPLOYMENT.md](./PRODUCTION_DEPLOYMENT.md)** - Detailed deployment instructions

### For Updates
- **[UPDATE_GUIDE.md](./UPDATE_GUIDE.md)** - ⭐ Comprehensive update guide with troubleshooting
- **[QUICK_UPDATE.md](./QUICK_UPDATE.md)** - ⚡ Quick reference card (bookmark this!)
- **[update-production.sh](./update-production.sh)** - Automated update script

## 🔄 Updating Production

### Quick Update (5 minutes)

If you already have the site deployed and need to update it with latest changes:

**Option 1: Use the automated script**
```bash
ssh your-username@micronesian.school
cd ~/domains/micronesian.school/public_html
./update-production.sh
```

**Option 2: Manual update (see QUICK_UPDATE.md)**
```bash
ssh your-username@micronesian.school
cd ~/domains/micronesian.school/public_html
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

**For detailed instructions:** See [UPDATE_GUIDE.md](./UPDATE_GUIDE.md)

## 🛠️ Technical Stack

- **Framework:** Laravel 12.x
- **Frontend:** Livewire 3.6.4, Alpine.js, Tailwind CSS
- **Admin Panel:** FilamentPHP 3.3.37
- **Authentication:** Laravel Breeze 2.3.8
- **Database:** MySQL 8.0
- **Languages:** PHP 8.2, JavaScript, HTML, CSS

## 📦 Local Development Setup

### Using Docker (Recommended)

```bash
# Clone repository
git clone https://github.com/ShvedkoDev/book_library.git
cd book_library

# Start Docker containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install && npm run build

# Set up environment
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --seed

# Create admin user
docker-compose exec app php artisan make:filament-user
```

Access the application:
- **Main Site:** http://localhost
- **Admin Panel:** http://localhost/admin
- **PHPMyAdmin:** http://localhost:8080

### Manual Setup (Without Docker)

```bash
# Install dependencies
composer install
npm install && npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env, then:
php artisan migrate --seed

# Create admin
php artisan make:filament-user

# Start dev server
php artisan serve
```

## 📖 Documentation Structure

```
.
├── PROJECT_README.md           # This file - Project overview
├── CLAUDE.md                   # Complete project documentation
├── TODO_REDESIGN.md           # Development roadmap
│
├── DEPLOYMENT_README.md       # Main deployment guide
├── DEPLOYMENT_CHECKLIST.md    # Quick deployment steps
├── PRODUCTION_DEPLOYMENT.md   # Detailed deployment instructions
│
├── UPDATE_GUIDE.md            # ⭐ Comprehensive update guide
├── QUICK_UPDATE.md            # ⚡ Quick update reference
└── update-production.sh       # Automated update script
```

## 🎯 Key Features

### Guide Module
- Landing page with project information
- Resource contributors list
- Terms of use
- Navigation to library

### Library Module
- **Search & Filter:** Real-time search with advanced filters (Subject, Grade, Language, Type, Year)
- **Book Management:** Complete CRUD via FilamentPHP admin panel
- **User Features:** Authentication, bookmarks, reviews, notes, reading lists
- **Access Control:** Full access, limited access, unavailable books
- **Analytics:** Comprehensive tracking of views, downloads, searches, and filter usage
- **PDF Handling:** View and download educational materials
- **Related Content:** Discover books in same collection, language, or by same author

### Admin Features
- Complete book management (CRUD operations)
- User management and roles
- Review moderation (approve/reject)
- Analytics dashboard with:
  - Book view tracking
  - Download statistics
  - Search query analysis
  - Filter usage insights
- CSV import for bulk book data
- Access request management

## 🌐 Production Environment

- **Hosting:** Hostinger Cloud/Shared hosting
- **Domain:** micronesian.school
- **SSL:** Enabled via Hostinger
- **Deployment Method:** Manual (Git + SSH)
- **Database:** MySQL 8.0

## 📊 Analytics & Tracking

The application includes comprehensive analytics:

- **Book Views:** Track every book page visit with user, IP, and timestamp
- **Downloads:** Monitor PDF downloads with full analytics
- **Search Queries:** Record all searches, including zero-result queries
- **Filter Usage:** Track popular filters to understand user behavior

All analytics accessible via admin panel at `/admin`

## 🔒 Security Features

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection on all outputs
- Password hashing with bcrypt
- Environment-based configuration
- Rate limiting on sensitive routes
- File upload validation
- User authentication and authorization

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📝 Recent Updates

Check the [UPDATE_GUIDE.md](./UPDATE_GUIDE.md) for instructions on updating your production site with the latest features:

**Latest improvements include:**
- Book page action button styling consistency
- Language dropdown positioning fixes
- Profile and activity page integration
- Smart pagination implementation
- Notes and reviews CRUD functionality
- Modal design improvements
- WordPress CSS conflict resolutions

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

### For Deployment Issues
- Check [UPDATE_GUIDE.md](./UPDATE_GUIDE.md) troubleshooting section
- Review Laravel logs: `storage/logs/laravel.log`
- Check [PRODUCTION_DEPLOYMENT.md](./PRODUCTION_DEPLOYMENT.md)

### For Hosting Issues
- **Hostinger hPanel:** https://hpanel.hostinger.com
- **Live Chat:** Available 24/7
- **Knowledge Base:** https://support.hostinger.com

### For Development Questions
- Review [CLAUDE.md](./CLAUDE.md) for technical details
- Check issue tracker on GitHub
- Review Laravel documentation: https://laravel.com/docs

## 📜 License

This project is proprietary software developed for the Government of the Federated States of Micronesia.

## 🙏 Acknowledgments

- **Client:** Government of the Federated States of Micronesia
- **Framework:** Laravel, FilamentPHP, Livewire
- **Inspiration:** OpenLibrary.org, Ulu Education Toolkit
- **Hosting:** Hostinger

---

**Repository:** https://github.com/ShvedkoDev/book_library
**Production:** https://micronesian.school
**Version:** 1.0
**Last Updated:** 2025-01-10
