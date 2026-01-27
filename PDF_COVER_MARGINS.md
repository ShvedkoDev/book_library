# PDF Cover Margins Implementation

## Change Summary
Added left and right margins to PDF cover generation for better visual presentation and content spacing.

## Implementation Details

### Margins Configuration
- **Left Margin**: 10mm
- **Right Margin**: 10mm
- **Content Width**: Page width minus margins (190mm for A4)

### Visual Layout

**Full-Width Elements** (no margins):
- Header gradient (top blue gradient bar)
- Footer gradient (bottom blue gradient bar)
- Footer tagline text

**Elements with Margins** (respect 10mm left/right margins):
- White content area background
- Resource Library banner background
- All HTML content (automatically constrained by PDF margins)

### Code Changes

**File**: `app/Services/PdfCoverService.php`

1. **Defined margin variables** (after line 96):
```php
$leftMargin = 10;   // 10mm left margin
$rightMargin = 10;  // 10mm right margin
```

2. **Updated SetMargins()** (line 106):
```php
$pdf->SetMargins($leftMargin, 0, $rightMargin);
```

3. **Calculated content width** (after AddPage):
```php
$contentWidth = $pageWidth - $leftMargin - $rightMargin;
```

4. **Updated white content background** (Layer 2):
```php
$pdf->Rect($leftMargin, 4, $contentWidth, 250, 'F');
```

5. **Updated banner background** (Layer 4):
```php
$pdf->Rect($leftMargin, 4, $contentWidth, 20, 'F');
```

### Visual Effect

**Before**: Content extended to page edges (0mm margins)
**After**: Content has 10mm white space on left and right sides

**Benefits**:
- Better visual balance
- Improved readability
- Professional appearance
- Consistent with standard document formatting
- Print-friendly (avoids edge trimming issues)

## Testing

Generate a PDF cover with download enabled to see the margins:
1. Go to `/admin/settings`
2. Enable "PDF Cover Download Enabled"
3. Download any book PDF
4. View the cover page - notice the white margins on left/right

## Customization

To adjust margin sizes, modify these values in `PdfCoverService.php` (lines ~98-99):

```php
$leftMargin = 10;   // Change value (in mm)
$rightMargin = 10;  // Change value (in mm)
```

Common margin sizes:
- **10mm** - Current setting (moderate margins)
- **15mm** - Wider margins (more white space)
- **5mm** - Narrow margins (more content space)
- **0mm** - No margins (original full-bleed design)

## Status
✅ Left and right margins implemented
✅ Full-width gradients maintained
✅ Content area properly constrained
✅ Syntax validated
✅ Caches cleared
