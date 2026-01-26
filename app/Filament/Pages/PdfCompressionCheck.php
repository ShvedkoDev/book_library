<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\BookFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PdfCompressionCheck extends Page implements HasTable
{
    use InteractsWithTable;

    private const STATS_CACHE_KEY = 'pdf_compression_full_stats';

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'PDF Compression Check';

    protected static ?string $title = 'PDF Compression Check';

    protected static ?string $navigationGroup = 'System Tools';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.pdf-compression-check';

    /**
     * Check if a PDF is compressed/readable by FPDI
     */
    public static function checkPdfCompression(string $filePath): array
    {
        // Check if file exists
        if (!file_exists($filePath)) {
            return [
                'status' => 'missing',
                'message' => 'File not found',
                'can_add_cover' => false,
            ];
        }

        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize === 0) {
            return [
                'status' => 'empty',
                'message' => 'Empty file',
                'can_add_cover' => false,
            ];
        }

        // Read PDF content for analysis
        $content = @file_get_contents($filePath, false, null, 0, min($fileSize, 1024 * 1024)); // Read first 1MB max
        $pdfVersion = null;
        $hasObjectStreams = false;
        $hasXRefStreams = false;

        if ($content !== false) {
            // Extract PDF version
            if (preg_match('/%PDF-(\d\.\d)/', $content, $matches)) {
                $pdfVersion = $matches[1];
            }

            // Check for Object Streams (PDF 1.5+)
            $hasObjectStreams = (bool)preg_match('/\/ObjStm/', $content);

            // Check for XRef Streams (PDF 1.5+)
            $hasXRefStreams = (bool)preg_match('/\/XRefStm/', $content);
        }

        // Try to read with FPDI
        try {
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $pageCount = $pdf->setSourceFile($filePath);

            return [
                'status' => 'normal',
                'message' => "OK ({$pageCount} pages)" . ($pdfVersion ? " - PDF {$pdfVersion}" : ''),
                'can_add_cover' => true,
                'pages' => $pageCount,
                'pdf_version' => $pdfVersion,
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Check for Object Streams (most common issue with free FPDI parser)
            if ($hasObjectStreams || $hasXRefStreams) {
                return [
                    'status' => 'object_streams',
                    'message' => "PDF {$pdfVersion} - Uses Object Streams (needs paid parser or conversion)",
                    'can_add_cover' => false,
                    'error' => $errorMessage,
                    'pdf_version' => $pdfVersion,
                    'has_object_streams' => $hasObjectStreams,
                    'has_xref_streams' => $hasXRefStreams,
                ];
            }

            // Check if it's a compression issue
            if (stripos($errorMessage, 'compression') !== false ||
                stripos($errorMessage, 'filter') !== false ||
                stripos($errorMessage, 'flate') !== false) {
                return [
                    'status' => 'compressed',
                    'message' => 'Compressed - ' . substr($errorMessage, 0, 60),
                    'can_add_cover' => false,
                    'error' => $errorMessage,
                    'pdf_version' => $pdfVersion,
                ];
            }

            // Other error
            return [
                'status' => 'error',
                'message' => 'Read error: ' . substr($errorMessage, 0, 100),
                'can_add_cover' => false,
                'error' => $errorMessage,
                'pdf_version' => $pdfVersion,
            ];
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BookFile::query()->where('file_type', 'pdf')->with('book'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('book.title')
                    ->label('Book Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->book?->title),

                TextColumn::make('filename')
                    ->label('Filename')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->filename),

                BadgeColumn::make('compression_status')
                    ->label('Status')
                    ->getStateUsing(function (BookFile $record): string {
                        $cacheKey = "pdf_compression_check_{$record->id}";

                        return Cache::remember($cacheKey, 3600, function () use ($record) {
                            $filePath = storage_path('app/public/' . $record->file_path);
                            $result = self::checkPdfCompression($filePath);
                            return $result['status'];
                        });
                    })
                    ->colors([
                        'success' => 'normal',
                        'danger' => 'object_streams',
                        'warning' => 'compressed',
                        'warning' => 'error',
                        'secondary' => 'missing',
                        'secondary' => 'empty',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'normal',
                        'heroicon-o-exclamation-circle' => 'object_streams',
                        'heroicon-o-exclamation-triangle' => 'compressed',
                        'heroicon-o-x-circle' => 'error',
                        'heroicon-o-question-mark-circle' => 'missing',
                        'heroicon-o-document' => 'empty',
                    ]),

                TextColumn::make('pdf_version')
                    ->label('PDF Ver')
                    ->getStateUsing(function (BookFile $record): string {
                        $cacheKey = "pdf_compression_check_{$record->id}";

                        return Cache::remember($cacheKey . '_version', 3600, function () use ($record) {
                            $filePath = storage_path('app/public/' . $record->file_path);
                            $result = self::checkPdfCompression($filePath);
                            return $result['pdf_version'] ?? '—';
                        });
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('details')
                    ->label('Details')
                    ->getStateUsing(function (BookFile $record): string {
                        $cacheKey = "pdf_compression_check_{$record->id}";

                        return Cache::remember($cacheKey . '_message', 3600, function () use ($record) {
                            $filePath = storage_path('app/public/' . $record->file_path);
                            $result = self::checkPdfCompression($filePath);
                            return $result['message'];
                        });
                    })
                    ->wrap(),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(function (BookFile $record): string {
                        $filePath = storage_path('app/public/' . $record->file_path);
                        if (file_exists($filePath)) {
                            $bytes = filesize($filePath);
                            if ($bytes >= 1073741824) {
                                return number_format($bytes / 1073741824, 2) . ' GB';
                            } elseif ($bytes >= 1048576) {
                                return number_format($bytes / 1048576, 2) . ' MB';
                            } elseif ($bytes >= 1024) {
                                return number_format($bytes / 1024, 2) . ' KB';
                            }
                            return $bytes . ' B';
                        }
                        return 'N/A';
                    }),

                TextColumn::make('book_id')
                    ->label('Book ID')
                    ->sortable()
                    ->url(fn (BookFile $record): string => $record->book
                        ? route('filament.admin.resources.books.edit', ['record' => $record->book_id])
                        : '#'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Compression Status')
                    ->options([
                        'normal' => '✅ Normal (Can add cover)',
                        'object_streams' => '❌ Object Streams (PDF 1.5+) - Needs conversion',
                        'compressed' => '⚠️ Other compression issue',
                        'error' => '⚠️ Read error',
                        'missing' => 'Missing File',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where(function ($q) use ($data) {
                                // This will be filtered client-side since we calculate status dynamically
                            });
                        }
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view_file')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BookFile $record) => route('library.view-pdf', [
                        'book' => $record->book_id,
                        'file' => $record->id
                    ]))
                    ->openUrlInNewTab(),

                \Filament\Tables\Actions\Action::make('recheck')
                    ->label('Recheck')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (BookFile $record) {
                        Cache::forget("pdf_compression_check_{$record->id}");
                        Cache::forget("pdf_compression_check_{$record->id}_message");
                         Cache::forget(self::STATS_CACHE_KEY);
                    })
                    ->requiresConfirmation(false),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'desc')
            ->poll('30s');
    }

    public function getPdfStatisticsProperty(): array
    {
        return Cache::remember(self::STATS_CACHE_KEY, 600, function () {
            $stats = [
                'total' => 0,
                'normal' => 0,
                'object_streams' => 0,
                'compressed' => 0,
                'error' => 0,
                'missing' => 0,
                'empty' => 0,
            ];

            BookFile::where('file_type', 'pdf')
                ->orderBy('id')
                ->chunk(200, function ($files) use (&$stats) {
                    foreach ($files as $file) {
                        $stats['total']++;

                        $status = Cache::remember("pdf_compression_check_{$file->id}", 3600, function () use ($file) {
                            $filePath = storage_path('app/public/' . $file->file_path);
                            $result = self::checkPdfCompression($filePath);

                            return $result['status'];
                        });

                        if (! array_key_exists($status, $stats)) {
                            $stats[$status] = 0;
                        }

                        $stats[$status]++;
                    }
                });

            $stats['last_updated'] = now();

            return $stats;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_cache')
                ->label('Clear Cache & Recheck All')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    Cache::flush();
                    $this->dispatch('$refresh');
                })
                ->requiresConfirmation(),

            Action::make('export_object_streams')
                ->label('Export Object Streams List (PDF 1.5+)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $objectStreams = [];
                    $maxBatchSize = 100 * 1024 * 1024; // 100MB in bytes

                    BookFile::where('file_type', 'pdf')->with('book')->chunk(100, function ($files) use (&$objectStreams) {
                        foreach ($files as $file) {
                            $filePath = storage_path('app/public/' . $file->file_path);
                            $result = self::checkPdfCompression($filePath);

                            if ($result['status'] === 'object_streams') {
                                // Get file size
                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                
                                $objectStreams[] = [
                                    'id' => $file->id,
                                    'book_id' => $file->book_id,
                                    'book_title' => $file->book?->title,
                                    'filename' => $file->filename,
                                    'file_path' => $file->file_path,
                                    'pdf_version' => $result['pdf_version'] ?? 'Unknown',
                                    'message' => $result['message'],
                                    'file_size' => $fileSize,
                                ];
                            }
                        }
                    });

                    // Split into batches based on file size
                    $batches = $this->splitIntoBatches($objectStreams, $maxBatchSize);
                    
                    // Always create ZIP with actual PDF files (not CSV)
                    return $this->createBatchedZipDownload($batches, 'object-streams-pdfs', 'object_streams');
                }),

            Action::make('export_all_issues')
                ->label('Export All Problem PDFs')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->action(function () {
                    $problems = [];
                    $maxBatchSize = 100 * 1024 * 1024; // 100MB in bytes

                    BookFile::where('file_type', 'pdf')->with('book')->chunk(100, function ($files) use (&$problems) {
                        foreach ($files as $file) {
                            $filePath = storage_path('app/public/' . $file->file_path);
                            $result = self::checkPdfCompression($filePath);

                            if (in_array($result['status'], ['object_streams', 'compressed', 'error'])) {
                                // Get file size
                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                
                                $problems[] = [
                                    'id' => $file->id,
                                    'book_id' => $file->book_id,
                                    'book_title' => $file->book?->title,
                                    'filename' => $file->filename,
                                    'file_path' => $file->file_path,
                                    'status' => $result['status'],
                                    'pdf_version' => $result['pdf_version'] ?? 'Unknown',
                                    'message' => $result['message'],
                                    'file_size' => $fileSize,
                                ];
                            }
                        }
                    });

                    // Split into batches based on file size
                    $batches = $this->splitIntoBatches($problems, $maxBatchSize);
                    
                    // Always create ZIP with actual PDF files (not CSV)
                    return $this->createBatchedZipDownload($batches, 'all-problem-pdfs', 'all_issues');
                }),
        ];
    }

    /**
     * Split items into batches based on file size (max 100MB per batch)
     */
    protected function splitIntoBatches(array $items, int $maxBatchSize): array
    {
        $batches = [];
        $currentBatch = [];
        $currentBatchSize = 0;

        foreach ($items as $item) {
            $fileSize = $item['file_size'] ?? 0;

            // If adding this item exceeds max size, start new batch
            if ($currentBatchSize + $fileSize > $maxBatchSize && !empty($currentBatch)) {
                $batches[] = $currentBatch;
                $currentBatch = [];
                $currentBatchSize = 0;
            }

            $currentBatch[] = $item;
            $currentBatchSize += $fileSize;
        }

        // Add last batch if not empty
        if (!empty($currentBatch)) {
            $batches[] = $currentBatch;
        }

        return $batches;
    }

    /**
     * Generate CSV content for a batch of items
     */
    protected function generateCsvForBatch(array $items, string $type): string
    {
        if ($type === 'object_streams') {
            $csv = "ID,Book ID,Book Title,Filename,File Path,PDF Version,Status,File Size (MB)\n";
            foreach ($items as $item) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $item['id'],
                    $item['book_id'],
                    str_replace('"', '""', $item['book_title'] ?? ''),
                    str_replace('"', '""', $item['filename']),
                    $item['file_path'],
                    $item['pdf_version'],
                    $item['message'],
                    number_format(($item['file_size'] ?? 0) / 1024 / 1024, 2)
                );
            }
        } else {
            $csv = "ID,Book ID,Book Title,Filename,File Path,Issue Type,PDF Version,Details,File Size (MB)\n";
            foreach ($items as $item) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $item['id'],
                    $item['book_id'],
                    str_replace('"', '""', $item['book_title'] ?? ''),
                    str_replace('"', '""', $item['filename']),
                    $item['file_path'],
                    $item['status'],
                    $item['pdf_version'],
                    str_replace('"', '""', $item['message']),
                    number_format(($item['file_size'] ?? 0) / 1024 / 1024, 2)
                );
            }
        }

        return $csv;
    }

    /**
     * Create a ZIP file with actual PDF files (batched by size)
     * Each batch ZIP contains up to 100MB of PDF files
     */
    protected function createBatchedZipDownload(array $batches, string $prefix, string $type)
    {
        // For multiple batches, create separate ZIP files
        if (count($batches) > 1) {
            return $this->createMultipleBatchZips($batches, $prefix, $type);
        }

        // Single batch - create one ZIP with PDFs
        $zipFilename = $prefix . '-' . date('Y-m-d') . '.zip';
        $tempZipPath = storage_path('app/temp/' . $zipFilename);

        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        // Create ZIP archive
        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Could not create ZIP file');
        }

        // Add README
        $batch = $batches[0];
        $readme = $this->generateBatchReadme($batch, 1, 1);
        $zip->addFromString('README.txt', $readme);

        // Add CSV index file
        $csv = $this->generateCsvForBatch($batch, $type);
        $zip->addFromString('file-list.csv', $csv);

        // Add actual PDF files
        foreach ($batch as $item) {
            $filePath = storage_path('app/public/' . $item['file_path']);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($item['file_path']));
            }
        }

        $zip->close();

        // Return the ZIP file and delete after sending
        return response()->download($tempZipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Create multiple batch ZIP files and package them in a master ZIP
     */
    protected function createMultipleBatchZips(array $batches, string $prefix, string $type)
    {
        $masterZipFilename = $prefix . '-all-batches-' . date('Y-m-d') . '.zip';
        $tempMasterZipPath = storage_path('app/temp/' . $masterZipFilename);

        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        // Create master ZIP
        $masterZip = new \ZipArchive();
        if ($masterZip->open($tempMasterZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Could not create master ZIP file');
        }

        // Add master README
        $totalFiles = array_sum(array_map('count', $batches));
        $totalSize = 0;
        foreach ($batches as $batch) {
            foreach ($batch as $item) {
                $totalSize += $item['file_size'] ?? 0;
            }
        }

        $masterReadme = "PDF Export - Multiple Batches\n";
        $masterReadme .= str_repeat("=", 60) . "\n\n";
        $masterReadme .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
        $masterReadme .= "Total Batches: " . count($batches) . "\n";
        $masterReadme .= "Total Files: " . $totalFiles . "\n";
        $masterReadme .= "Total Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n\n";
        $masterReadme .= "IMPORTANT: This archive has been split into " . count($batches) . " separate ZIP files\n";
        $masterReadme .= "to avoid timeout issues on shared hosting (max 100MB per batch).\n\n";
        $masterReadme .= "Instructions:\n";
        $masterReadme .= str_repeat("-", 60) . "\n";
        $masterReadme .= "1. Extract this master ZIP file\n";
        $masterReadme .= "2. You will find " . count($batches) . " batch ZIP files inside\n";
        $masterReadme .= "3. Extract each batch ZIP to get the PDF files\n";
        $masterReadme .= "4. Convert PDFs using: ./convert_pdfs_batch.sh\n";
        $masterReadme .= "5. Upload converted PDFs back to server\n\n";
        $masterReadme .= "Batch Details:\n";
        $masterReadme .= str_repeat("-", 60) . "\n";

        foreach ($batches as $index => $batch) {
            $batchNum = $index + 1;
            $batchSize = 0;
            foreach ($batch as $item) {
                $batchSize += $item['file_size'] ?? 0;
            }
            $masterReadme .= sprintf("batch-%02d.zip: %d files, %.2f MB\n", $batchNum, count($batch), $batchSize / 1024 / 1024);
        }

        $masterReadme .= "\nConversion Workflow:\n";
        $masterReadme .= str_repeat("-", 60) . "\n";
        $masterReadme .= "For each batch:\n";
        $masterReadme .= "  1. Extract batch-XX.zip to a folder\n";
        $masterReadme .= "  2. cd into that folder\n";
        $masterReadme .= "  3. Run: ../convert_pdfs_batch.sh\n";
        $masterReadme .= "  4. Converted files will be in ./converted/ folder\n";
        $masterReadme .= "  5. Upload files from ./converted/ back to server\n\n";
        $masterReadme .= "See: BATCH_PDF_CONVERSION_GUIDE.md for detailed instructions\n";

        $masterZip->addFromString('README.txt', $masterReadme);

        // Create each batch ZIP and add to master
        $tempBatchZips = [];
        foreach ($batches as $index => $batch) {
            $batchNum = $index + 1;
            $batchZipFilename = sprintf('batch-%02d.zip', $batchNum);
            $tempBatchZipPath = storage_path('app/temp/' . $batchZipFilename);
            $tempBatchZips[] = $tempBatchZipPath;

            // Create batch ZIP
            $batchZip = new \ZipArchive();
            if ($batchZip->open($tempBatchZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Could not create batch ZIP file');
            }

            // Add batch README
            $batchReadme = $this->generateBatchReadme($batch, $batchNum, count($batches));
            $batchZip->addFromString('README.txt', $batchReadme);

            // Add CSV file list
            $csv = $this->generateCsvForBatch($batch, $type);
            $batchZip->addFromString('file-list.csv', $csv);

            // Add actual PDF files
            foreach ($batch as $item) {
                $filePath = storage_path('app/public/' . $item['file_path']);
                if (file_exists($filePath)) {
                    $batchZip->addFile($filePath, basename($item['file_path']));
                }
            }

            $batchZip->close();

            // Add batch ZIP to master ZIP
            $masterZip->addFile($tempBatchZipPath, $batchZipFilename);
        }

        $masterZip->close();

        // Clean up temporary batch ZIPs
        foreach ($tempBatchZips as $tempZip) {
            if (file_exists($tempZip)) {
                @unlink($tempZip);
            }
        }

        // Return the master ZIP file and delete after sending
        return response()->download($tempMasterZipPath, $masterZipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Generate README content for a single batch
     */
    protected function generateBatchReadme(array $batch, int $batchNum, int $totalBatches): string
    {
        $batchSize = 0;
        foreach ($batch as $item) {
            $batchSize += $item['file_size'] ?? 0;
        }

        $readme = "PDF Batch " . $batchNum . " of " . $totalBatches . "\n";
        $readme .= str_repeat("=", 60) . "\n\n";
        $readme .= "Batch Number: " . $batchNum . " / " . $totalBatches . "\n";
        $readme .= "Files in this batch: " . count($batch) . "\n";
        $readme .= "Total size: " . number_format($batchSize / 1024 / 1024, 2) . " MB\n";
        $readme .= "Export Date: " . date('Y-m-d H:i:s') . "\n\n";
        $readme .= "Contents:\n";
        $readme .= str_repeat("-", 60) . "\n";
        $readme .= "- README.txt (this file)\n";
        $readme .= "- file-list.csv (list of all PDFs with metadata)\n";
        $readme .= "- " . count($batch) . " PDF files ready to convert\n\n";
        $readme .= "Conversion Instructions:\n";
        $readme .= str_repeat("-", 60) . "\n";
        $readme .= "1. Place convert_pdfs_batch.sh in the parent directory\n";
        $readme .= "2. Open terminal and cd to this batch folder\n";
        $readme .= "3. Run: chmod +x ../convert_pdfs_batch.sh\n";
        $readme .= "4. Run: ../convert_pdfs_batch.sh\n";
        $readme .= "5. Wait for conversion to complete\n";
        $readme .= "6. Converted files will be in ./converted/ folder\n";
        $readme .= "7. Upload files from ./converted/ back to server\n\n";
        $readme .= "Windows Users:\n";
        $readme .= "Use convert_pdfs_batch.bat instead\n\n";
        $readme .= "For detailed instructions, see: BATCH_PDF_CONVERSION_GUIDE.md\n";

        return $readme;
    }
}
