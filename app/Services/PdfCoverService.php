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
     * @return string Temporary path to the merged PDF
     */
    public function generatePdfWithCover(Book $book, string $bookPdfPath, $user = null): string
    {
        // Create new PDF document
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add cover page
        $this->addCoverPage($pdf, $book, $user);

        // Try to import the book PDF, decompress if needed
        try {
            $pageCount = $pdf->setSourceFile($bookPdfPath);
        } catch (\Exception $e) {
            // If FPDI can't read it due to compression, try to decompress first
            if (strpos($e->getMessage(), 'compression') !== false) {
                \Log::info('PDF compression detected, attempting to decompress: ' . basename($bookPdfPath));
                $decompressedPath = $this->decompressPdf($bookPdfPath);
                if ($decompressedPath) {
                    $pageCount = $pdf->setSourceFile($decompressedPath);
                    $bookPdfPath = $decompressedPath; // Use decompressed version
                } else {
                    throw $e; // Re-throw if decompression failed
                }
            } else {
                throw $e;
            }
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

        // Clean up decompressed file if we created one
        if (isset($decompressedPath) && file_exists($decompressedPath)) {
            @unlink($decompressedPath);
        }

        return $tempPath;
    }

    /**
     * Decompress a PDF using Ghostscript or PHP fallback
     *
     * @param string $pdfPath
     * @return string|null Path to decompressed PDF, or null if failed
     */
    protected function decompressPdf(string $pdfPath): ?string
    {
        $outputPath = storage_path('app/temp/decompressed_' . uniqid() . '.pdf');
        
        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        // Method 1: Try Ghostscript (if available on server)
        if ($this->isGhostscriptAvailable()) {
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
                escapeshellarg($outputPath),
                escapeshellarg($pdfPath)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($outputPath)) {
                \Log::info('PDF decompressed successfully using Ghostscript');
                return $outputPath;
            }
        }

        // Method 2: Try bundled QPDF (pure-PHP deployment safe) or system qpdf
        if ($qpdfBinary = $this->getQpdfBinary()) {
            $command = sprintf(
                'LD_LIBRARY_PATH=%s %s --stream-data=uncompress %s %s 2>&1',
                escapeshellarg(dirname($qpdfBinary, 2) . '/lib'),
                escapeshellarg($qpdfBinary),
                escapeshellarg($pdfPath),
                escapeshellarg($outputPath)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($outputPath)) {
                \Log::info('PDF decompressed successfully using QPDF');
                return $outputPath;
            }
        }

        \Log::warning('PDF decompression failed - no suitable tools available');
        return null;
    }

    /**
     * Check if Ghostscript is available
     */
    protected function isGhostscriptAvailable(): bool
    {
        exec('which gs 2>&1', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Get QPDF binary path (bundled first, then system)
     */
    protected function getQpdfBinary(): ?string
    {
        $bundled = storage_path('tools/qpdf/bin/qpdf');
        if (is_file($bundled) && is_executable($bundled)) {
            return $bundled;
        }

        exec('which qpdf 2>&1', $output, $returnVar);
        return $returnVar === 0 ? trim($output[0] ?? '') : null;
    }

    /**
     * Add cover page with book metadata
     *
     * @param Fpdi $pdf
     * @param Book $book
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     */
    protected function addCoverPage(Fpdi $pdf, Book $book, $user = null): void
    {
        // Create a new page from scratch (no template import needed)
        // Using standard Letter size (216mm x 279mm)
        $pdf->AddPage('P', 'LETTER');
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(false);

        // HEADER SECTION - Logo and Title Area
        // Add header background color (light blue/teal)
        $pdf->SetFillColor(200, 230, 240);
        $pdf->Rect(0, 0, 216, 60, 'F');

        // Main Title
        $pdf->SetXY(20, 25);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(0, 70, 110); // Dark blue
        $pdf->MultiCell(176, 0, $book->title, 0, 'C', false, 1);

        // Subtitle (if exists)
        if ($book->subtitle) {
            $pdf->SetXY(20, $pdf->GetY() + 3);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetTextColor(0, 90, 140);
            $pdf->MultiCell(176, 0, $book->subtitle, 0, 'C', false, 1);
        }

        // Reset text color
        $pdf->SetTextColor(0, 0, 0);

        // METADATA SECTION
        $yPos = 80;
        $lineHeight = 8;

        // Authors
        $authors = $book->authors->pluck('name')->join('; ');
        if ($authors) {
            $this->addMetadataRow($pdf, 'Author(s)', $authors, $yPos);
            $yPos += $lineHeight;
        }

        // Publisher
        if ($book->publisher) {
            $this->addMetadataRow($pdf, 'Publisher', $book->publisher->name, $yPos);
            $yPos += $lineHeight;
        }

        // Publication Year
        if ($book->publication_year) {
            $this->addMetadataRow($pdf, 'Publication Year', $book->publication_year, $yPos);
            $yPos += $lineHeight;
        }

        // Languages
        $languages = $book->languages->pluck('name')->join(', ');
        if ($languages) {
            $this->addMetadataRow($pdf, 'Language', $languages, $yPos);
            $yPos += $lineHeight;
        }

        // Collection
        if ($book->collection) {
            $this->addMetadataRow($pdf, 'Collection', $book->collection->name, $yPos);
            $yPos += $lineHeight;
        }

        // Purpose/Subject
        $purposes = $book->purposeClassifications->pluck('value')->join(', ');
        if ($purposes) {
            $this->addMetadataRow($pdf, 'Purpose', $purposes, $yPos);
            $yPos += $lineHeight;
        }

        // Grade Level
        $gradeLevels = $book->learnerLevelClassifications->pluck('value')->join(', ');
        if ($gradeLevels) {
            $this->addMetadataRow($pdf, 'Grade Level', $gradeLevels, $yPos);
            $yPos += $lineHeight;
        }

        // ISBN
        $isbn = $book->getIdentifier('isbn');
        if ($isbn) {
            $this->addMetadataRow($pdf, 'ISBN', $isbn, $yPos);
            $yPos += $lineHeight;
        }

        // Pages
        if ($book->pages) {
            $this->addMetadataRow($pdf, 'Pages', $book->pages, $yPos);
            $yPos += $lineHeight;
        }

        // FOOTER - Generation Info
        $pdf->SetXY(20, 260);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $timestamp = now()->format('m/d/Y g:i a');
        $generatedBy = $user ? $user->name : "Guest";
        $pdf->MultiCell(176, 0, "Document generated on {$timestamp} by {$generatedBy}", 0, 'C');
        
        // Project Info
        $pdf->SetXY(20, 270);
        $pdf->MultiCell(176, 0, "FSM National Vernacular Language Arts (VLA) Curriculum", 0, 'C');
    }

    /**
     * Add a metadata row with label and value
     */
    protected function addMetadataRow(Fpdi $pdf, string $label, string $value, float $yPos): void
    {
        // Label (bold)
        $pdf->SetXY(20, $yPos);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 6, $label . ':', 0, 0, 'L');
        
        // Value (normal)
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(126, 6, $value, 0, 'L', false, 1, 70, $yPos);
    }
}
