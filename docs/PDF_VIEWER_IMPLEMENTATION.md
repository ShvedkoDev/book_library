# PDF Viewer Implementation - Limited Access Protection

## Overview
This implementation provides different PDF viewing experiences based on access levels, similar to island.education's approach.

## Technology: PDF.js
We use **Mozilla's PDF.js** - the same library used by Firefox and many educational platforms.

### Why PDF.js?
- ✅ Renders PDFs in HTML5 Canvas
- ✅ No browser plugins required
- ✅ Prevents direct PDF downloads for limited access
- ✅ Allows watermarking for limited access content
- ✅ Open source and well-maintained
- ✅ Works in all modern browsers

## Access Levels

### 1. Full Access (`access_level = 'full'`)
**User Experience:**
- Clicks "View PDF" → Opens browser's native PDF viewer
- Full functionality: zoom, print, download, text selection
- URL: `/library/book/{id}/view-pdf/{fileId}`
- Download button available

**Technical Implementation:**
- Direct PDF file streaming
- Headers: `Content-Disposition: inline`
- Browser handles rendering
- Full control for user

### 2. Limited Access (`access_level = 'limited'`)
**User Experience:**
- Clicks "View PDF" → Opens custom canvas-based viewer
- Can view and navigate pages
- **Cannot download** (button hidden)
- **Cannot print** (browser print disabled on canvas)
- **Watermarked** with "LIMITED ACCESS"
- URL: `/library/book/{id}/viewer/{fileId}`

**Technical Implementation:**
- PDF.js renders to canvas element
- Right-click disabled
- Text selection disabled
- Download button not shown
- Watermark overlay
- Custom navigation controls

### 3. Unavailable (`access_level = 'unavailable'`)
- Both view and download blocked
- Shows 403 error

## Protection Features for Limited Access

### 1. Canvas Rendering
- PDF rendered as images on canvas
- Cannot "Save As" from browser
- No direct access to PDF file

### 2. Disabled Browser Features
```javascript
// Disable right-click
canvas.addEventListener('contextmenu', (e) => e.preventDefault());

// Disable text selection
canvas.style.userSelect = 'none';
canvas.style.webkitUserSelect = 'none';
```

### 3. Watermarking
```html
<div class="pdf-watermark">LIMITED ACCESS</div>
```
- Transparent overlay
- Rotated 45 degrees
- Visible on every page
- Cannot be removed by user

### 4. No Download Button
- Download button only shown for full access
- PDF.js viewer doesn't expose download functionality

### 5. Custom Toolbar
- Only navigation controls shown
- No download, print, or save options
- Custom page navigation
- Zoom controls only

## Routes

### `/library/book/{book}/viewer/{file}`
- Route name: `library.view-pdf`
- Controller: `LibraryController@viewPdfViewer`
- Shows viewer page with PDF.js
- Used for limited access

### `/library/book/{book}/view-pdf/{file}`
- Route name: `library.view-pdf-direct`
- Controller: `LibraryController@viewPdf`
- Streams PDF file directly
- Used by PDF.js to load the file
- Used for full access direct viewing

### `/library/book/{book}/download/{file}`
- Route name: `library.download`
- Controller: `LibraryController@download`
- Forces download (Content-Disposition: attachment)
- Only available for full access

## How It Works

### Limited Access Flow:
1. User clicks "View PDF" button
2. Routes to `/library/book/{id}/viewer/{file}`
3. `viewPdfViewer()` controller method:
   - Checks access level
   - Loads `pdf-viewer.blade.php` view
4. PDF.js in browser:
   - Fetches PDF from `/library/book/{id}/view-pdf/{file}`
   - Renders each page to canvas
   - Shows custom controls
   - Displays watermark

### Full Access Flow:
1. User clicks "View PDF" button
2. Routes to `/library/book/{id}/viewer/{file}`
3. `viewPdfViewer()` detects full access
4. Redirects to `/library/book/{id}/view-pdf/{file}`
5. Browser's native PDF viewer opens

## Security Considerations

### What This PREVENTS:
- ✅ Easy downloading via browser
- ✅ Right-click save
- ✅ Drag-and-drop save
- ✅ Browser print function on canvas
- ✅ Text copying from canvas

### What This CANNOT Prevent:
- ❌ Screenshots (user can screenshot pages)
- ❌ Screen recording
- ❌ Browser DevTools inspection (advanced users)
- ❌ PDF.js source manipulation (very advanced users)

### Best For:
- Educational content
- Preview/sample viewing
- Reducing casual piracy
- Encouraging legitimate purchases/access

## Comparison with Other Methods

### island.education Approach (Hash-based)
```
https://island.education/product.html#example-assets/books/file.pdf
```
- Uses hash navigation
- PDF.js or similar library
- Same canvas rendering approach
- Same protection level

### Our Approach (Route-based)
```
https://yourdomain.com/library/book/1/viewer/1
```
- Cleaner URLs
- Better SEO
- More Laravel-conventional
- Same protection level
- Better analytics tracking

## Files Modified/Created

### New Files:
- `resources/views/library/pdf-viewer.blade.php` - PDF.js viewer page

### Modified Files:
- `app/Http/Controllers/LibraryController.php` - Added `viewPdfViewer()` method
- `routes/web.php` - Added viewer route

### External Dependencies:
- PDF.js CDN: https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/

## Usage

### In Blade Views:
```blade
@if($book->access_level === 'full')
    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}"
       target="_blank">View PDF</a>
@elseif($book->access_level === 'limited')
    <a href="{{ route('library.view-pdf', ['book' => $book->id, 'file' => $pdfFile->id]) }}">
       View PDF (Limited)
    </a>
@endif
```

## Future Enhancements

### Possible Additions:
1. **Page Limits** - Limit preview to first X pages
2. **Time Limits** - Auto-close after X minutes
3. **Analytics** - Track which pages users view
4. **Dynamic Watermarks** - Include user email/name
5. **DRM Integration** - More advanced protection
6. **Server-side Rendering** - Render to images on server

## Testing

### Test Full Access:
1. Create/update book with `access_level = 'full'`
2. Click "View PDF"
3. Should open in browser's native viewer
4. Should have download button

### Test Limited Access:
1. Create/update book with `access_level = 'limited'`
2. Click "View PDF"
3. Should open custom viewer
4. Should show watermark
5. Should NOT have download button
6. Right-click should be disabled

## Maintenance

### Updating PDF.js:
Check for updates at: https://github.com/mozilla/pdf.js/releases

Update CDN URLs in `pdf-viewer.blade.php`:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/[VERSION]/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
  'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/[VERSION]/pdf.worker.min.js';
</script>
```

## Support

For issues or questions about PDF viewing:
- PDF.js Documentation: https://mozilla.github.io/pdf.js/
- PDF.js GitHub: https://github.com/mozilla/pdf.js
