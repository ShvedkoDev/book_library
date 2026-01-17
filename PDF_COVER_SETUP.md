# PDF Cover Page System - Setup Guide

## Overview
The system automatically adds a custom cover page to all PDF files when viewing or downloading from the library. The cover page includes book metadata, publication info, and a timestamp showing when and by whom the document was generated.

## How It Works
1. User clicks "View PDF" or "Download PDF"
2. System generates a cover page with book metadata
3. Cover page is merged with the original book PDF
4. Combined PDF is served to the user

## Requirements

### PHP Packages (Already Installed via Composer)
- ✅ `setasign/fpdi` - PDF manipulation library
- ✅ `tecnickcom/tcpdf` - PDF generation library

### System Tools (Need to be installed on production server)

The system tries to read PDFs directly, but many PDFs use compression that requires decompression first. Install **ONE** of these tools:

#### Option 1: Ghostscript (Recommended)
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ghostscript

# Verify installation
gs --version
```

#### Option 2: QPDF (Alternative)
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install qpdf

# Verify installation
qpdf --version
```

## Installation on Production Server

### Step 1: Install System Tools
Choose Ghostscript (recommended) or QPDF and install it:

```bash
ssh your-production-server
sudo apt-get update
sudo apt-get install ghostscript
```

### Step 2: Verify PHP Extensions
Check that your PHP installation has these basic extensions (should already be present):
```bash
php -m | grep -E "gd|mbstring"
```

### Step 3: Deploy Code
Use the existing deployment script:
```bash
./scripts/deploy-quick.sh
```

Or if there are composer changes:
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Step 4: Set Permissions
Ensure the temp directory is writable:
```bash
chmod -R 775 storage/app/temp
```

### Step 5: Test
1. Navigate to any book in the library
2. Click "View PDF" or "Download PDF"
3. Check that the PDF has a cover page with book information
4. Check the logs if there are issues:
```bash
tail -f storage/logs/laravel.log
```

## How It Handles Different PDFs

### Scenario 1: Uncompressed PDFs
- ✅ Works immediately with FPDI
- ⚡ Fast generation (< 1 second)

### Scenario 2: Compressed PDFs (WITH Ghostscript/QPDF)
- ✅ Automatically decompresses PDF
- ✅ Merges with cover page
- ⏱️ Slightly slower (1-3 seconds depending on PDF size)

### Scenario 3: Compressed PDFs (WITHOUT tools)
- ⚠️ Falls back to original PDF without cover
- 📝 Logs warning message
- 🔄 System continues to work normally

## Cover Page Contents

The generated cover page includes:

### Header Section (Blue background)
- Book Title (large, centered)
- Subtitle (if available)

### Metadata Section
- Author(s)
- Publisher
- Publication Year
- Language(s)
- Collection
- Purpose/Subject
- Grade Level
- ISBN
- Page Count

### Footer Section
- Generation timestamp: "Document generated on 01/17/2026 3:45 PM by John Doe"
- Project name: "FSM National Vernacular Language Arts (VLA) Curriculum"

## Troubleshooting

### Issue: PDFs are served without cover pages

**Check 1: Look at Laravel logs**
```bash
tail -100 storage/logs/laravel.log | grep "PDF cover"
```

**Check 2: Verify Ghostscript is installed**
```bash
which gs
gs --version
```

**Check 3: Test Ghostscript manually**
```bash
gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH \
   -sOutputFile=/tmp/test_output.pdf \
   storage/app/public/books/some-book.pdf
```

**Check 4: Permissions**
```bash
ls -la storage/app/temp/
# Should show drwxrwxr-x
```

### Issue: "Permission denied" errors
```bash
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

### Issue: Slow PDF generation
- This is normal for large PDFs with many pages
- Consider increasing PHP execution time in production:
```php
// In php.ini or .htaccess
max_execution_time = 120
memory_limit = 256M
```

## Performance Considerations

### Generation Times (approximate)
- Small PDF (< 10 pages): 1-2 seconds
- Medium PDF (10-50 pages): 2-5 seconds
- Large PDF (50+ pages): 5-15 seconds

### Memory Usage
- Each PDF generation uses 50-150MB of RAM
- Temporary files are automatically cleaned up
- Adjust PHP memory_limit if needed

## Monitoring

### Success Indicators
- No errors in Laravel logs
- PDF files are larger than originals (due to cover page)
- Users see cover page when opening PDFs

### Log Messages
```
✅ PDF decompressed successfully using Ghostscript
✅ PDF cover page generated successfully
⚠️ PDF decompression failed - no suitable tools available
⚠️ PDF cover generation failed: [error details]
```

## Deployment Checklist

- [ ] Ghostscript or QPDF installed on server
- [ ] PHP extensions verified (gd, mbstring)
- [ ] Composer packages installed
- [ ] Storage permissions set (775)
- [ ] Test PDF viewing works
- [ ] Test PDF downloading works
- [ ] Check Laravel logs for errors
- [ ] Verify cover page appears correctly
- [ ] Test with multiple different books
- [ ] Monitor server performance

## Support

If issues persist:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Verify file permissions on storage/ directory
4. Test Ghostscript/QPDF manually with sample PDF
5. Check PHP memory_limit and max_execution_time settings

## Without Ghostscript/QPDF

If you cannot install system tools, the system will:
- ✅ Still work for uncompressed PDFs
- ⚠️ Fall back to original PDF (without cover) for compressed PDFs
- 📝 Log which PDFs couldn't have cover pages added

This is acceptable for basic functionality, but installing Ghostscript is recommended for full coverage.
