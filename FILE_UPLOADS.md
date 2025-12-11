# File Upload Management

This guide explains how to use the File Upload feature in the admin panel to manage any type of file.

## Overview

The File Upload feature allows administrators to upload, manage, and organize any file type (PDFs, images, documents, videos, archives, etc.) to the `storage/app/uploads/` directory.

## Accessing File Uploads

Navigate to the admin panel and look for **"File Uploads"** in the **"Content Management"** section of the navigation menu.

## Uploading Files

1. Click on **"Create"** button in the File Uploads section
2. Click the file upload area or drag & drop your file
3. Wait for the file to upload
4. The system will automatically detect:
   - Original file name
   - File type (MIME type)
   - File size
5. Optionally add a description for the file
6. Click **"Create"** to save

**Upload Limits:**
- Maximum file size: 100MB
- Accepted file types: All types (no restrictions)
- Storage location: `storage/app/uploads/`

## Managing Uploaded Files

### File List View

The file list shows:
- **File Name**: Click to copy the name
- **Type**: MIME type badge (e.g., application/pdf, image/png)
- **Size**: Human-readable format (KB, MB, GB)
- **Description**: Optional file description
- **Uploaded By**: User who uploaded the file
- **Uploaded At**: Date and time of upload

### Available Actions

For each file, you can:

#### Download
- Click the **"Download"** button to download the file with its original name

#### Copy Path
- Click **"Copy Path"** to view file paths in a modal:
  - **Relative Path**: Path from `storage/app/` (e.g., `uploads/xyz123.pdf`)
  - **Absolute Path**: Full server path
  - Click "Copy" buttons to copy paths to clipboard
  - Shows Laravel usage example: `Storage::download('uploads/xyz123.pdf')`

#### Edit
- Update the description or replace the file
- Note: Replacing the file will update all metadata automatically

#### Delete
- Permanently removes the file from both database and storage
- Requires confirmation

### Bulk Actions

Select multiple files and:
- **Bulk Delete**: Delete multiple files at once with confirmation

### Filtering & Sorting

**Filters:**
- Filter by **File Type** (MIME type) using the dropdown

**Sorting:**
- Sort by any column (name, type, size, date)
- Default: Most recent uploads first

**Search:**
- Search by file name, type, or description

## Using Uploaded Files in Your Application

### In Laravel Controllers/Models

```php
use Illuminate\Support\Facades\Storage;

// Download a file
return Storage::download('uploads/xyz123.pdf', 'custom-name.pdf');

// Get file contents
$contents = Storage::get('uploads/xyz123.pdf');

// Get file URL (if configured for public access)
$url = Storage::url('uploads/xyz123.pdf');

// Check if file exists
if (Storage::exists('uploads/xyz123.pdf')) {
    // File exists
}
```

### In Blade Views

```blade
{{-- Link to download file --}}
<a href="{{ route('file.download', $fileUpload->id) }}">
    Download {{ $fileUpload->original_name }}
</a>

{{-- Display file information --}}
<p>File: {{ $fileUpload->original_name }}</p>
<p>Size: {{ $fileUpload->formatted_size }}</p>
<p>Type: {{ $fileUpload->mime_type }}</p>
```

### Querying Files Programmatically

```php
use App\Models\FileUpload;

// Get all files
$files = FileUpload::all();

// Find by original name
$file = FileUpload::where('original_name', 'document.pdf')->first();

// Get files by type
$pdfs = FileUpload::where('mime_type', 'application/pdf')->get();

// Get recent uploads
$recent = FileUpload::latest()->take(10)->get();

// Get files uploaded by specific user
$userFiles = FileUpload::where('uploaded_by', $userId)->get();

// Access file properties
echo $file->original_name;  // Original filename
echo $file->file_path;      // Storage path
echo $file->formatted_size; // Human-readable size
echo $file->full_path;      // Absolute server path
```

## File Organization Tips

1. **Use Descriptions**: Add clear descriptions to help identify files later
2. **Consistent Naming**: Upload files with descriptive names
3. **Regular Cleanup**: Periodically review and remove unused files
4. **File Types**: Use the filter to quickly find specific file types
5. **Search**: Use the search feature to locate files by name or description

## Storage Structure

```
storage/
└── app/
    └── uploads/           # All uploaded files stored here
        ├── abc123.pdf
        ├── xyz456.docx
        ├── def789.jpg
        └── ...
```

Files are stored with hashed names to prevent conflicts and ensure uniqueness.

## Troubleshooting

### Upload Fails
- Check file size (max 100MB)
- Verify storage directory permissions: `chmod -R 775 storage/app/uploads`
- Check disk space on server

### File Not Found
- Ensure the file wasn't deleted from storage manually
- Check that the file path in database matches actual location
- Verify storage directory exists

### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Large File Uploads
If you need to upload files larger than 100MB:

1. Edit the resource file: `app/Filament/Resources/FileUploadResource.php`
2. Change `->maxSize(102400)` to your desired size in KB
3. Update PHP settings if needed:
   - `upload_max_filesize` in php.ini
   - `post_max_size` in php.ini

## Security Notes

- Files are stored in `storage/app/` which is NOT publicly accessible by default
- Files can only be accessed through Laravel's download routes
- Only authenticated admin users can upload/manage files
- File paths are tracked in the database for audit purposes

## Database Schema

The `file_uploads` table stores:
- `id`: Unique identifier
- `original_name`: Original filename
- `file_name`: Hashed filename (unique)
- `file_path`: Relative path from storage/app/
- `mime_type`: File MIME type
- `file_size`: Size in bytes
- `description`: Optional description
- `uploaded_by`: User ID who uploaded
- `created_at`: Upload timestamp
- `updated_at`: Last modification timestamp

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify file permissions on storage directory
3. Ensure sufficient disk space
4. Check PHP upload limits if uploading large files
