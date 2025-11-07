# Deployment Scripts

This directory contains production-ready deployment and maintenance scripts for the Micronesian Teachers Digital Library project.

## Available Scripts

### `deploy-csv-system.sh`

Automates the deployment checklist for the CSV Import/Export system (CSV_IMPORT_TODO.md section 12.3).

**What it does:**
1. ✅ Checks prerequisites (Laravel project, PHP/Docker availability)
2. ✅ Runs database migrations
3. ✅ Creates required storage directories
4. ✅ Sets proper permissions (775) on CSV directories
5. ✅ Verifies CSV templates exist

**Usage:**

```bash
# Basic usage (auto-detects Docker environment)
./scripts/deploy-csv-system.sh

# Dry-run mode (see what would be done without making changes)
./scripts/deploy-csv-system.sh --dry-run

# Verbose mode (show detailed output)
./scripts/deploy-csv-system.sh --verbose

# Dry-run with verbose output
./scripts/deploy-csv-system.sh --dry-run --verbose

# Force Docker environment
./scripts/deploy-csv-system.sh --docker

# Force local PHP (no Docker)
./scripts/deploy-csv-system.sh --no-docker

# Skip permission changes (useful for restrictive environments)
./scripts/deploy-csv-system.sh --skip-perms

# Show help
./scripts/deploy-csv-system.sh --help
```

**Exit Codes:**
- `0` - Success
- `1` - General error
- `2` - Prerequisites not met
- `3` - Migration failed
- `4` - Directory creation failed
- `5` - Permission change failed

**Logging:**

All operations are logged to `storage/logs/csv-deploy-TIMESTAMP.log` for auditing and troubleshooting.

**Example Output:**

```
============================================
  CSV System Deployment Script
============================================

➜ Checking prerequisites...
✓ Prerequisites check passed

➜ Running database migrations...
✓ Database migrations

➜ Creating storage directories...
✓ All storage directories verified

➜ Setting directory permissions...
✓ Directory permissions configured

➜ Verifying CSV templates...
✓ All required CSV templates verified

============================================
  CSV System Deployment Summary
============================================

Total tasks:      8
Completed:        8
Skipped:          0
Failed:           0

Environment:      Docker
Log file:         storage/logs/csv-deploy-20251107-222437.log

✓ Deployment completed successfully!

Next steps:
  1. Review the log file for details
  2. Test CSV import/export in admin panel
  3. Check /admin/csv-import to verify system is ready
```

## Environment Detection

The scripts automatically detect whether you're running in a Docker environment or using local PHP:

- **Docker mode**: Detected if `docker-compose.yml` exists and containers are running
- **Local PHP mode**: Used if no Docker environment is detected

You can override auto-detection using `--docker` or `--no-docker` flags.

## Best Practices

### Before Running Scripts

1. **Backup your database** (for production deployments):
   ```bash
   php artisan db:backup --reason=pre-deployment
   ```

2. **Test in staging first**:
   ```bash
   ./scripts/deploy-csv-system.sh --dry-run --verbose
   ```

3. **Review what will be done**:
   - Check the dry-run output
   - Verify environment detection is correct
   - Ensure you have necessary permissions

### Production Deployment

```bash
# Step 1: Dry-run to verify
./scripts/deploy-csv-system.sh --dry-run --verbose

# Step 2: If everything looks good, run for real
./scripts/deploy-csv-system.sh --verbose

# Step 3: Verify in admin panel
# Navigate to: http://your-domain/admin/csv-import
```

### CI/CD Integration

For automated deployments, you can use exit codes to handle errors:

```bash
#!/bin/bash
# Example CI/CD deploy script

if ./scripts/deploy-csv-system.sh; then
    echo "CSV system deployed successfully"
else
    echo "CSV system deployment failed"
    exit 1
fi
```

## Troubleshooting

### Script won't execute

```bash
# Fix line endings (if you get "required file not found" error)
sed -i 's/\r$//' scripts/deploy-csv-system.sh

# Make executable
chmod +x scripts/deploy-csv-system.sh
```

### Permission denied errors

```bash
# Run with sudo (not recommended for Docker environments)
sudo ./scripts/deploy-csv-system.sh

# Or skip permission changes
./scripts/deploy-csv-system.sh --skip-perms
```

### Docker containers not running

```bash
# Start Docker containers first
docker-compose up -d

# Then run deployment
./scripts/deploy-csv-system.sh
```

### Check logs for details

```bash
# View latest deployment log
tail -f storage/logs/csv-deploy-*.log

# Or use the path shown in script output
cat /path/to/log/file
```

## Development

### Adding New Scripts

When adding new deployment scripts:

1. Follow the same structure (help, logging, error handling)
2. Use color-coded output for readability
3. Include `--dry-run` mode
4. Document in this README
5. Make executable: `chmod +x scripts/your-script.sh`
6. Fix line endings: `sed -i 's/\r$//' scripts/your-script.sh`

### Script Template

See `deploy-csv-system.sh` as a reference template for creating new deployment scripts.

## Related Documentation

- **CSV Import/Export System**: See `/docs/CSV_FIELD_MAPPING.md`
- **Bulk Upload Guide**: See `/docs/BULK_UPLOAD_GUIDE.md`
- **Deployment Checklist**: See `CSV_IMPORT_TODO.md` section 12.3
- **Quick Reference**: See `/docs/CSV_QUICK_REFERENCE.md`

## Support

For issues or questions about deployment scripts:
1. Check the log files in `storage/logs/`
2. Review the related documentation above
3. Run with `--verbose` flag for detailed output
4. Use `--dry-run` to test without making changes

## Version History

- **v1.0** (2025-11-07) - Initial release of `deploy-csv-system.sh`
  - Auto-detect Docker/PHP environment
  - Comprehensive error handling and logging
  - Dry-run mode for safe testing
  - Color-coded output with progress tracking
