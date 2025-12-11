# CMS Pages Backup & Import Guide

This guide explains how to backup CMS pages from your local environment and import them on production.

## Export CMS Pages (Local Environment)

Export all CMS pages with their content, custom HTML blocks, and relationships:

```bash
# Export to default file (pages-export.json)
docker-compose exec app php artisan pages:export

# Export to custom file name
docker-compose exec app php artisan pages:export --file=my-backup.json
```

The export file will be saved to: `storage/app/pages-export.json`

**What Gets Exported:**
- Page title, slug, content
- Custom HTML blocks
- Meta description and keywords
- Publication status and settings
- Resource contributor relationships
- Parent-child page relationships
- Timestamps and order

## Copy Export File to Production

After exporting, copy the JSON file to your production server:

```bash
# From your local machine
scp storage/app/pages-export.json user@production-server:/path/to/production/storage/app/
```

Or download it from local and upload to production via FTP/SFTP.

## Import CMS Pages (Production Environment)

On your production server:

```bash
# Import (updates existing pages or creates new ones)
php artisan pages:import

# Import from custom file
php artisan pages:import --file=my-backup.json

# Fresh import (WARNING: Deletes all existing pages first!)
php artisan pages:import --fresh
```

### Import Behavior

- **Default**: Updates existing pages (matched by slug) or creates new ones
- **--fresh flag**: DELETES all existing pages before importing (use with caution!)

### What Happens During Import:

1. **Pages**: Creates or updates based on slug
2. **Parent Relationships**: Automatically resolved and linked
3. **Resource Contributors**: Created if they don't exist, then attached to pages
4. **Custom HTML Blocks**: Imported with the page content

## Example Workflow

### On Local Development:

```bash
# 1. Make changes to CMS pages in admin panel
# 2. Export pages
docker-compose exec app php artisan pages:export

# 3. Copy the file
# File location: /home/gena/book_library/storage/app/pages-export.json
```

### On Production Server:

```bash
# 1. Upload pages-export.json to storage/app/ directory

# 2. Run import
php artisan pages:import

# Output will show:
# - How many pages were created
# - How many pages were updated
# - Any errors or skipped pages
```

## Troubleshooting

### File Not Found
```
Error: File not found: /var/www/storage/app/pages-export.json
```
**Solution**: Make sure you uploaded the JSON file to the correct `storage/app/` directory.

### Permission Issues
```
Error: Permission denied
```
**Solution**: Ensure the web server has write permissions to the storage directory:
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Import Fails Midway
If import fails, pages that were successfully processed before the error will remain in the database. You can:
- Fix the issue in the JSON file
- Run import again (it will update existing pages)

## Safety Tips

✅ **ALWAYS** backup your production database before importing
✅ **TEST** the import on a staging environment first
✅ **VERIFY** the export file contents before uploading
⚠️ **NEVER** use `--fresh` flag on production unless you're absolutely sure

## Additional Options

### Check Export File Size
```bash
ls -lh storage/app/pages-export.json
```

### View Export Contents
```bash
head -n 50 storage/app/pages-export.json
```

### Validate JSON
```bash
cat storage/app/pages-export.json | jq .
```

## Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify JSON file format is valid
3. Ensure all required database tables exist (run migrations)
4. Check file permissions on storage directory
