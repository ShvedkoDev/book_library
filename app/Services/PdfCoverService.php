<?php

namespace App\Services;

use App\Models\Book;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfCoverService
{
    protected $templatePath;

    public function __construct()
    {
        $this->templatePath = base_path('pdf_precover_templates/cover page mockup.pdf');
    }

    /**
     * Generate a PDF with cover page prepended to the book PDF
     *
     * @param Book $book
     * @param string $bookPdfPath Full path to the book PDF file
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @param \App\Models\BookFile|null $bookFile The book file record (for digital_source)
     * @return string Temporary path to the merged PDF
     */
    public function generatePdfWithCover(Book $book, string $bookPdfPath, $user = null, $bookFile = null): string
    {
        // Create new PDF document with UTF-8 encoding
        // Parameters: orientation, unit, format, unicode=true, encoding='UTF-8'
        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set Unicode font to support special characters (accented letters, etc.)
        // FreeSans is visually similar to Arial/Helvetica (used on website)
        // and provides full Unicode support for accented characters
        // This is the closest match to "Proxima Nova, Helvetica, Arial" with Unicode
        $pdf->SetFont('freesans', '', 10, '', true);

        // Add cover page
        $this->addCoverPage($pdf, $book, $user, $bookFile);

        // Try to import the book PDF directly. If it fails, we fall back to serving the original.
        try {
            $pageCount = $pdf->setSourceFile($bookPdfPath);
        } catch (\Exception $e) {
            \Log::error('FPDI failed to read PDF for cover generation: ' . $e->getMessage() . ' - File: ' . basename($bookPdfPath));
            // Fall back to serving original PDF without cover
            return $bookPdfPath;
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        // Save to temporary file
        $tempPath = storage_path('app/temp/pdf_with_cover_' . uniqid() . '.pdf');

        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        $pdf->Output($tempPath, 'F');

        return $tempPath;
    }

    /**
     * Add cover page with book metadata
     *
     * @param Fpdi $pdf
     * @param Book $book
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @param \App\Models\BookFile|null $bookFile
     */
    protected function addCoverPage(Fpdi $pdf, Book $book, $user = null, $bookFile = null): void
    {
        // ========================================================================
        // STEP 1: DEFINE PAGE DIMENSIONS
        // ========================================================================

        // Set page width to US Letter standard (8.5 inches = 215.9mm)
        // This is the horizontal dimension of the page
        // Possible values: 210 (A4), 215.9 (US Letter), 216 (rounded US Letter)
        $pageWidth = 210;  // millimeters

        // Set page height to US Letter standard (11 inches = 279.4mm)
        // This is the vertical dimension of the page
        // Possible values: 297 (A4), 279.4 (US Letter)
        $pageHeight = 297; // millimeters

        // Define left and right margins (in millimeters)
        // These margins provide white space on the sides of the cover page
        $leftMargin = 10;   // 10mm left margin
        $rightMargin = 10;  // 10mm right margin

        // ========================================================================
        // STEP 2: CONFIGURE PDF SETTINGS TO PREVENT AUTO-LAYOUT
        // ========================================================================

        // SetMargins(left, top, right)
        // Sets page margins for content area
        // Parameters: left margin, top margin, right margin (all in mm)
        // Left/right margins create white space on sides
        $pdf->SetMargins($leftMargin, 0, $rightMargin);

        // SetAutoPageBreak(auto, margin)
        // Disables automatic page breaks that would create new pages when content exceeds height
        // Parameter 1 (bool): false = disable auto page break, true = enable
        // Parameter 2 (float): bottom margin in mm, set to 0 for no margin
        $pdf->SetAutoPageBreak(false, 0);

        // setCellPaddings(left, top, right, bottom)
        // Sets internal padding for table cells to zero
        // All parameters in mm: left, top, right, bottom padding
        // Possible values: 0 (no padding), 2 (2mm padding), 5 (5mm padding)
        $pdf->setCellPaddings(0, 0, 0, 0);

        // setCellMargins(left, top, right, bottom)
        // Sets external margins for table cells to zero
        // All parameters in mm: left, top, right, bottom margin
        // Possible values: 0 (no margin), 1 (1mm margin), 3 (3mm margin)
        $pdf->setCellMargins(0, 0, 0, 0);

        // ========================================================================
        // STEP 3: ADD NEW PAGE WITH EXACT DIMENSIONS
        // ========================================================================

        // AddPage(orientation, format)
        // Creates a new page in the PDF document
        // Parameter 1 (string): 'P' = Portrait orientation, 'L' = Landscape orientation
        // Parameter 2 (array): [width, height] in millimeters
        // Example values: 'P', [210, 297] for A4 portrait
        $pdf->AddPage('P', [$pageWidth, $pageHeight]);

        // Calculate content width (page width minus left and right margins)
        $contentWidth = $pageWidth - $leftMargin - $rightMargin;

        // ========================================================================
        // LAYER 1: DRAW HEADER GRADIENT BACKGROUND (FULL WIDTH)
        // ========================================================================

        // drawGradientRect(pdf, x, y, width, height, colorStart, colorEnd)
        // Draws a horizontal linear gradient rectangle at the top of the page
        // Mimics CSS: linear-gradient(15deg, #1d496a, #8198b2)
        // Header gradient extends full width for visual impact
        $this->drawGradientRect($pdf, 0, 0, $pageWidth, 4, [29, 73, 106], [129, 152, 178]);

        // ========================================================================
        // LAYER 2: DRAW WHITE CONTENT AREA BACKGROUND (RESPECTS MARGINS)
        // ========================================================================

        // SetFillColor(red, green, blue)
        // Sets the fill color for subsequent drawing operations
        // Parameters: RGB values from 0-255
        // [255, 255, 255] = white
        $pdf->SetFillColor(255, 255, 255);

        // Rect(x, y, width, height, style)
        // Draws a filled rectangle for the white content area with margins
        // Starts at left margin, reduced width to account for both margins
        $pdf->Rect($leftMargin, 4, $contentWidth, 250, 'F');

        // ========================================================================
        // LAYER 3: DRAW FOOTER GRADIENT BACKGROUND (FULL WIDTH)
        // ========================================================================

        // Calculate footer height dynamically to fill remaining space
        $footerHeight = $pageHeight - 280;

        // drawGradientRect for footer (same gradient as header)
        // Footer gradient extends full width for visual consistency
        $this->drawGradientRect($pdf, 0, 270, $pageWidth, $footerHeight, [29, 73, 106], [129, 152, 178]);

        // ========================================================================
        // LAYER 4: DRAW "RESOURCE LIBRARY" BANNER BACKGROUND (RESPECTS MARGINS)
        // ========================================================================

        // Draw background for "Resource library" banner with margins
        // Banner respects left/right margins for content alignment
        // Color: #c9d3e0 = RGB(201, 211, 224)
        $pdf->SetFillColor(201, 211, 224);

        // Rect(x, y, width, height, style)
        // Draws a filled rectangle for the banner background with margins
        $pdf->Rect($leftMargin, 4, $contentWidth, 20, 'F');

        // ========================================================================
        // LAYER 5: RENDER HTML CONTENT WITH BOOK METADATA
        // ========================================================================

        // buildCoverData(book, user, bookFile)
        // Prepares data array for the PDF cover template
        // Returns array with: book object, metadata, contributors, timestamps, etc.
        $data = $this->buildCoverData($book, $user, $bookFile);

        // view(template, data)->render()
        // Renders the Blade template 'resources/views/pdf/cover.blade.php' with data
        // Returns: HTML string with all book metadata formatted in tables
        $html = view('pdf.cover', $data)->render();

        // SetY(y)
        // Sets the current Y position for subsequent content
        // Parameter (float): Y position in mm (0 = top of page)
        // This ensures HTML rendering starts from the very top
        $pdf->SetY(0);

        // Ensure Unicode font is active for HTML rendering
        // FreeSans: closest TCPDF built-in match to Arial/Helvetica/Proxima Nova
        // - Visually similar to Arial and Helvetica
        // - Full Unicode support (accented letters: é, ú, á, etc.)
        // - Pre-installed with TCPDF (no custom font installation needed)
        // The 'true' parameter enables Unicode subsetting for smaller file sizes
        $pdf->SetFont('freesans', '', 10, '', true);

        // writeHTML(html, ln, fill, reseth, cell, align)
        // Renders HTML content onto the PDF page
        // Parameter 1 (string): HTML content to render
        // Parameter 2 (bool): true = add new line after, false = continue on same line
        // Parameter 3 (bool): false = do not fill background, true = fill
        // Parameter 4 (bool): false = do not reset height, true = reset
        // Parameter 5 (bool): false = no cell mode, true = cell mode
        // Parameter 6 (string): '' = left align, 'C' = center, 'R' = right
        $pdf->writeHTML($html, true, false, false, false, '');

        // ========================================================================
        // LAYER 6: REDRAW FOOTER GRADIENT TO COVER ANY CONTENT OVERFLOW
        // ========================================================================

        // Redraw the footer gradient on top of HTML content
        // This acts as a "mask" to hide any content that renders below Y=270mm
        // Uses same parameters as Layer 3 to ensure perfect overlay
        // This is necessary because HTML content might overflow beyond intended boundaries
        $this->drawGradientRect($pdf, 0, 270, $pageWidth, $footerHeight, [29, 73, 106], [129, 152, 178]);

        // ========================================================================
        // LAYER 7: ADD FOOTER TEXT (TAGLINE) OVER GRADIENT
        // ========================================================================

        // Calculate vertical center position for footer text
        // Formula: footer_start (264) + (footer_height / 2) - adjustment (3)
        // Example: 264 + (15.4 / 2) - 3 = 264 + 7.7 - 3 = 268.7mm
        // The -3 adjustment accounts for font height to achieve visual centering
        $footerTextY = 270 + ($footerHeight / 2) - 3;

        // SetXY(x, y)
        // Sets the current X and Y position for the footer text
        // Parameter 1 (float): X = 0mm (left edge, will be centered with Cell)
        // Parameter 2 (float): Y = 268.7mm (calculated vertical center)
        $pdf->SetXY(0, $footerTextY);

        // SetFont(family, style, size)
        // Sets the font for the footer tagline text
        // Using FreeSans italic to distinguish from main content while ensuring compatibility
        // Parameter 1 (string): 'freesans' = font family (built-in, Unicode-compatible)
        // Parameter 2 (string): 'I' = italic style, 'B' = bold, 'BI' = bold italic, '' = regular
        // Parameter 3 (int): 11 = font size in points
        // Parameter 4/5: Unicode support enabled
        $pdf->SetFont('freesans', 'I', 11, '', true);

        // SetTextColor(red, green, blue)
        // Sets the text color to white for visibility on dark gradient
        // Parameters: RGB values 0-255
        // [255, 255, 255] = white
        // Other examples: [0, 0, 0] = black, [100, 100, 100] = gray
        $pdf->SetTextColor(255, 255, 255);

        // Cell(width, height, text, border, ln, align)
        // Creates a single-line text cell for the tagline
        // Parameter 1 (float): Width = 215.9mm (full page width for centering)
        // Parameter 2 (float): Height = 6mm (cell height)
        // Parameter 3 (string): The tagline text to display
        // Parameter 4 (mixed): 0 = no border, 1 = border all sides, 'L'/'R'/'T'/'B' = specific sides
        // Parameter 5 (int): 0 = continue on same line, 1 = move to next line, 2 = move below
        // Parameter 6 (string): 'C' = center align, 'L' = left, 'R' = right, 'J' = justify
        $pdf->Cell($pageWidth, 6, 'Strengthening teaching and learning through the voices and languages of Micronesia.', 0, 0, 'C');
    }

    /**
     * Draw a horizontal gradient rectangle
     *
     * @param Fpdi $pdf
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @param array $colorStart RGB array [r, g, b]
     * @param array $colorEnd RGB array [r, g, b]
     */
    protected function drawGradientRect(Fpdi $pdf, float $x, float $y, float $width, float $height, array $colorStart, array $colorEnd): void
    {
        // TCPDF linear gradient
        $pdf->LinearGradient($x, $y, $width, $height, $colorStart, $colorEnd, [0, 0, 1, 0]);
    }

    protected function buildCoverData(Book $book, $user = null, $bookFile = null): array
    {
        $timestamp = now()->format('m/d/Y g:i a');
        $generatedBy = $user ? $user->name : "Guest";

        // Build logo paths and convert to base64 data URIs for TCPDF compatibility
        // TCPDF's writeHTML() doesn't handle local file paths well, but works with base64
        $logoFiles = ['NDOE.png', 'iREi-top.png', 'C4GTS.png'];
        $logos = collect($logoFiles)->map(function ($filename) {
            // Try different path possibilities
            $paths = [
                public_path('library-assets/images/' . $filename),
                base_path('public/library-assets/images/' . $filename),
                storage_path('app/public/library-assets/images/' . $filename),
            ];

            foreach ($paths as $path) {
                if (file_exists($path) && is_readable($path)) {
                    // Convert to base64 data URI for TCPDF compatibility
                    $imageData = base64_encode(file_get_contents($path));
                    $mimeType = mime_content_type($path);
                    $dataUri = "data:{$mimeType};base64,{$imageData}";

                    \Log::info('Logo found and converted to base64: ' . $filename . ' at ' . $path);
                    return $dataUri;
                }
            }

            \Log::warning('Logo not found: ' . $filename . ' - tried paths: ' . implode(', ', $paths));
            return null;
        })->filter()->values()->all();

        $metaFirstRowFirstCol = ['label' => 'Publication year', 'value' => $book->publication_year ?: '—'];

        $metaFirstRowSecondCol = ['label' => 'Language(s)', 'value' => $book->languages->pluck('name')->filter()->join(', ') ?: '—'];

        $metaSecondRowFirstCol = ['label' => 'Number of pages', 'value' => $book->pages ?: '—'];

        $metaSecondRowSecondCol = ['label' => 'Type', 'value' => optional($book->physicalType)->name ?? ($book->typeClassifications->pluck('value')->first() ?? '—')];

        $contributors = [
            ['label' => 'Author(s)', 'value' => $book->authors->pluck('name')->filter()->join('; ') ?: '—'],
            ['label' => 'Illustrator(s)', 'value' => $book->illustrators->pluck('name')->filter()->join('; ') ?: '—'],
        ];

        $editionNotes = [
            ['label' => 'Publisher', 'value' => optional($book->publisher)->name ?: '—'],
            ['label' => 'Project/partner', 'value' => $book->program_partner_name ?: ($book->collection->name ?? '—')],
        ];

        $classifications = [
            ['label' => 'Purpose', 'value' => $book->purposeClassifications->pluck('value')->join(', ') ?: '—'],
            ['label' => 'Genre', 'value' => $book->genreClassifications->pluck('value')->join(', ') ?: '—'],
            ['label' => 'Sub-genre', 'value' => $book->subgenreClassifications->pluck('value')->join(', ') ?: '—'],
        ];

        $notesText = collect([$book->notes_content ?? null, $book->notes_version ?? null, $book->notes_issue ?? null])
            ->filter()
            ->implode("\n\n");

        $bookFileId = optional($book->primaryPdf)->id ?? optional($book->files()->where('file_type', 'pdf')->first())->id;
        $downloadUrl = $bookFileId ? url("/library/book/{$book->id}/download/{$bookFileId}") : url("/library/book/{$book->id}");

        return [
            'book' => $book,
            'publisherLabel' => trim(optional($book->publisher)->name ?: 'Resource library'),
            'subtitle' => trim($book->subtitle ?? ''),
            'translated_title' => trim($book->translated_title ?? ''),
            'metaFirst' => $metaFirstRowFirstCol,
            'metaSecond' => $metaFirstRowSecondCol,
            'metaThird' => $metaSecondRowFirstCol,
            'metaForth' => $metaSecondRowSecondCol,
            'contributors' => $contributors,
            'editionNotes' => $editionNotes,
            'classifications' => $classifications,
            'description' => $book->description ?? '',
            'abstract' => $book->abstract ?? '',
            'notes' => $notesText,
            'downloadUrl' => $downloadUrl,
            'timestamp' => $timestamp,
            'generatedBy' => $generatedBy,
            'logos' => $logos,
            'digital_source' => $bookFile ? $bookFile->digital_source : '',
        ];
    }

    /**
     * Add a metadata row with label and value
     * Note: This method is currently unused (cover uses HTML rendering)
     */
    protected function addMetadataRow(Fpdi $pdf, string $label, string $value, float $yPos): void
    {
        // Label (bold)
        $pdf->SetXY(20, $yPos);
        $pdf->SetFont('freesans', 'B', 10, '', true);
        $pdf->Cell(50, 6, $label . ':', 0, 0, 'L');

        // Value (normal)
        $pdf->SetFont('freesans', '', 10, '', true);
        $pdf->MultiCell(126, 6, $value, 0, 'L', false, 1, 70, $yPos);
    }
}
