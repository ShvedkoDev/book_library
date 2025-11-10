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

**🌟 Recommended: Simple Git-Only Method**
- **[SIMPLE_UPDATE.md](./SIMPLE_UPDATE.md)** - ⚡ **Easiest method** - No scp/rsync! (Start here)
- **[build-and-commit.sh](./build-and-commit.sh)** - Build and commit assets locally
- **[update-simple.sh](./update-simple.sh)** - Update server (pulls from git, copies files)

**Advanced: Direct Upload Methods**
- **[UPDATE_GUIDE_HOSTINGER.md](./UPDATE_GUIDE_HOSTINGER.md)** - Upload via rsync/scp (app_root + public_html)
- **[QUICK_UPDATE_HOSTINGER.md](./QUICK_UPDATE_HOSTINGER.md)** - Quick reference with uploads
- **[update-server.sh](./update-server.sh)** - Server update (waits for upload)
- **[deploy-assets.sh](./deploy-assets.sh)** - Local deployment (rsync/scp)

**Generic Guides**
- **[UPDATE_GUIDE.md](./UPDATE_GUIDE.md)** - For servers with npm installed
- **[QUICK_UPDATE.md](./QUICK_UPDATE.md)** - Generic quick reference

## 🔄 Updating Production (Your Hostinger Setup)

### 🌟 Recommended: Simple Git-Only Update (5 minutes)

**No scp/rsync needed - Everything via git!**

**Step 1: Build and Commit Locally**
```bash
cd /home/gena/book_library
./build-and-commit.sh
```

**Step 2: Update Server**
```bash
ssh your-username@micronesian.school
cd ~/app_root
./update-simple.sh
```

**Done!** The script pulls from git and copies assets to public_html.

**See:** [SIMPLE_UPDATE.md](./SIMPLE_UPDATE.md) for complete guide

---

### Alternative: Upload Assets Directly (rsync/scp)

If you prefer not to commit built assets to git:

**Part 1: Update Server (waits for upload)**
```bash
ssh your-username@micronesian.school
cd ~/app_root
./update-server.sh
```

**Part 2: Build and Upload from Local**
```bash
cd /home/gena/book_library
./deploy-assets.sh
```

**See:** [UPDATE_GUIDE_HOSTINGER.md](./UPDATE_GUIDE_HOSTINGER.md) for this method

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
