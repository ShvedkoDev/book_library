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
        // Page dimensions: US Letter (8.5" x 11")
        $pageWidth = 215.9;  // mm
        $pageHeight = 279.4; // mm

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage('P', [$pageWidth, $pageHeight]);

        // Layer 1: Header gradient (0 to 14mm) - linear-gradient(15deg, #1d496a, #8198b2)
        $this->drawGradientRect($pdf, 0, 0, $pageWidth, 14, [29, 73, 106], [129, 152, 178]);

        // Layer 2: White content area (14mm to 264mm = 250mm)
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 14, $pageWidth, 250, 'F');

        // Layer 3: Footer gradient (264mm to 279.4mm = ~15mm) - same as header
        $footerHeight = $pageHeight - 264;
        $this->drawGradientRect($pdf, 0, 264, $pageWidth, $footerHeight, [29, 73, 106], [129, 152, 178]);

        // Layer 4: HTML content overlay
        $data = $this->buildCoverData($book, $user);
        $html = view('pdf.cover', $data)->render();
        $pdf->SetY(0);
        $pdf->writeHTML($html, true, false, false, false, '');

        // Layer 5: Footer text over the gradient (centered vertically in footer)
        $footerTextY = 264 + ($footerHeight / 2) - 3; // Center in footer gradient
        $pdf->SetXY(0, $footerTextY);
        $pdf->SetFont('marckscript', '', 11);
        $pdf->SetTextColor(255, 255, 255);
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

    protected function buildCoverData(Book $book, $user = null): array
    {
        $timestamp = now()->format('m/d/Y g:i a');
        $generatedBy = $user ? $user->name : "Guest";

        $logos = collect([
            public_path('library-assets/images/C4GTS.png'),
            public_path('library-assets/images/iREi-top.png'),
            public_path('library-assets/images/NDOE.png'),
        ])->filter(fn ($path) => file_exists($path))->values()->all();

        $meta = [
            ['label' => 'Publication year', 'value' => $book->publication_year ?: '—'],
            ['label' => 'Language(s)', 'value' => $book->languages->pluck('name')->filter()->join(', ') ?: '—'],
            ['label' => 'Number of pages', 'value' => $book->pages ?: '—'],
            ['label' => 'Type', 'value' => optional($book->physicalType)->name ?? ($book->typeClassifications->pluck('value')->first() ?? '—')],
        ];

        $contributors = [
            ['label' => 'Author(s)', 'value' => $book->authors->pluck('name')->filter()->join(', ') ?: '—'],
            ['label' => 'Illustrator(s)', 'value' => $book->illustrators->pluck('name')->filter()->join(', ') ?: '—'],
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
            'subtitle' => trim($book->subtitle ?: ($book->translated_title ?? '')),
            'meta' => $meta,
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
        ];
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
