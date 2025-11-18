# Deployment Scripts

This directory contains all deployment and maintenance scripts for the Micronesian Teachers Digital Library.

## Important: Build Workflow

**The production server does NOT have Node.js/npm installed.** Assets must be built locally and committed to Git.

**Quick Workflow (using helper script):**
```bash
# On local machine
./scripts/build-and-commit.sh "Your commit message"
git push origin main

# On production server
./scripts/deploy-production.sh
```

**Manual Workflow:**
1. Make changes locally
2. Build assets: `npm run build`
3. Commit build files: `git add . && git commit -m "message"`
4. Push to GitHub: `git push origin main`
5. Deploy on server: `./scripts/deploy-production.sh`

## Available Scripts

### 🔨 build-and-commit.sh
**Local helper - Build and commit assets** (LOCAL MACHINE ONLY)

```bash
./scripts/build-and-commit.sh "Your commit message"
```

**When to use:**
- On your local development machine
- Before pushing changes to Git
- Automates build and commit process

**What it does:**
1. Checks for uncommitted changes
2. Runs `npm install` if needed
3. Builds production assets
4. Adds all files including build directory
5. Creates commit with your message
6. Shows next steps (push and deploy)

**Duration:** ~30-60 seconds

---

### 🚀 deploy-production.sh
**Full production deployment script**

```bash
./scripts/deploy-production.sh
```

**When to use:**
- Major updates
- Database migrations
- New composer dependencies
- First-time deployment

**What it does:**
1. Enables maintenance mode
2. Pulls latest code from GitHub (includes pre-built assets)
3. Installs composer dependencies (production)
4. Runs database migrations
5. Verifies pre-built assets exist
6. Copies all files to public_html
7. Clears and rebuilds caches
8. Optimizes Laravel
9. Disables maintenance mode
10. Creates deployment log

**Duration:** ~1-3 minutes

---

### ⚡ deploy-quick.sh
**Quick deployment for minor updates**

```bash
./scripts/deploy-quick.sh
```

**When to use:**
- Code changes only (no new dependencies)
- Template/view updates
- Minor bug fixes
- Pre-built asset updates

**What it does:**
1. Pulls latest code (with pre-built assets)
2. Copies build files to public_html
3. Clears view cache

**Duration:** ~10-20 seconds

**Do NOT use for:**
- Database migrations
- New composer packages
- Major updates

---

### ✅ check-deployment-readiness.sh
**Pre-deployment environment checker**

```bash
./scripts/check-deployment-readiness.sh
```

**When to use:**
- Before first deployment
- After server configuration changes
- Troubleshooting deployment issues
- Verifying server environment

**What it checks:**
- Directory structure
- Git repository status
- PHP installation and extensions
- Composer installation
- Pre-built assets exist
- Laravel configuration (.env)
- File permissions
- Database connection
- public_html setup

**Exit codes:**
- `0` = All checks passed or only warnings
- `1` = Errors found, fix before deploying

---

### ↩️ rollback.sh
**Rollback to previous commit**

```bash
./scripts/rollback.sh [number_of_commits]
```

**Examples:**
```bash
./scripts/rollback.sh     # Rollback 1 commit
./scripts/rollback.sh 2   # Rollback 2 commits
```

**When to use:**
- Deployment introduced bugs
- Need to revert to previous version
- Emergency rollback

**What it does:**
1. Shows current and target commit
2. Asks for confirmation
3. Enables maintenance mode
4. Resets Git to previous commit
5. Runs full deployment script

**⚠️ Warning:** This permanently removes commits from your branch. Only use if you haven't pushed to GitHub or coordinate with team.

---

## Quick Reference

### First-Time Setup
```bash
# 1. Check server is ready
./scripts/check-deployment-readiness.sh

# 2. Run full deployment
./scripts/deploy-production.sh
```

### Regular Updates
```bash
# For major updates (migrations, dependencies)
./scripts/deploy-production.sh

# For minor updates (code only)
./scripts/deploy-quick.sh
```

### Emergency Rollback
```bash
./scripts/rollback.sh
```

### Verify Server
```bash
./scripts/check-deployment-readiness.sh
```

## Deployment Workflow

### Option 1: Safe Deployment (Recommended)
```bash
# 1. Check environment
./scripts/check-deployment-readiness.sh

# 2. If checks pass, deploy
./scripts/deploy-production.sh

# 3. Verify site is working
# If issues found, rollback:
./scripts/rollback.sh
```

### Option 2: Quick Update
```bash
# For small changes only
./scripts/deploy-quick.sh
```

## Common Issues

### "Permission denied" error
```bash
chmod +x ./scripts/*.sh
```

### "artisan not found" error
Make sure you're in the `app_root` directory:
```bash
cd ~/domains/micronesian.school/app_root
```

### Git pull fails
Check Git credentials:
```bash
git config --list
git pull origin main
```

### Build fails
Check npm dependencies:
```bash
npm install
npm run build
```

### Database migration fails
Check database connection:
```bash
php artisan db:show
php artisan migrate --pretend  # Preview migrations
```

## Script Maintenance

### Testing Scripts Locally
Before running on production, test in development:
```bash
# In local environment
./scripts/deploy-production.sh
```

### Customizing Scripts
Edit scripts for your specific needs:
- Deployment log location
- Cache clearing strategy
- Additional post-deployment tasks
- Custom file copying logic

### Adding New Scripts
1. Create in `/scripts/` directory
2. Make executable: `chmod +x scripts/your-script.sh`
3. Add to this README
4. Test thoroughly before using in production

## Security Notes

- ✅ Scripts use `set -e` (exit on error)
- ✅ Maintenance mode during deployment
- ✅ Production-only composer dependencies
- ✅ Confirmation prompts for destructive operations
- ✅ Deployment logs created
- ⚠️ Never commit `.env` file
- ⚠️ Review deployment logs after each deployment

## Support

For issues or questions:
1. Check `DEPLOYMENT.md` in project root
2. Review deployment logs in app_root
3. Check Laravel logs: `storage/logs/laravel.log`
4. Review this README

## File Locations

```
book_library/
├── scripts/
│   ├── deploy-production.sh          # Full deployment
│   ├── deploy-quick.sh                # Quick updates
│   ├── check-deployment-readiness.sh  # Environment check
│   ├── rollback.sh                    # Emergency rollback
│   └── README.md                      # This file
└── DEPLOYMENT.md                      # Detailed deployment guide
```
