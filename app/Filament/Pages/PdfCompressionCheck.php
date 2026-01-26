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
    private const BATCHES_CACHE_KEY = 'pdf_export_batches';

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
        $actions = [
            Action::make('clear_cache')
                ->label('Clear Cache & Recheck All')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    Cache::flush();
                    $this->dispatch('$refresh');
                })
                ->requiresConfirmation(),

            Action::make('prepare_object_streams')
                ->label('Prepare Object Streams Export')
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
                    
                    // Store batches in cache for download buttons
                    Cache::put(self::BATCHES_CACHE_KEY . '_object_streams', $batches, 3600);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Export Prepared')
                        ->body(count($batches) . ' batches ready (' . count($objectStreams) . ' files total). Download buttons will appear below.')
                        ->success()
                        ->send();
                        
                    $this->dispatch('$refresh');
                }),

            Action::make('prepare_all_issues')
                ->label('Prepare All Problem PDFs Export')
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
                    
                    // Store batches in cache for download buttons
                    Cache::put(self::BATCHES_CACHE_KEY . '_all_issues', $batches, 3600);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Export Prepared')
                        ->body(count($batches) . ' batches ready (' . count($problems) . ' files total). Download buttons will appear below.')
                        ->success()
                        ->send();
                        
                    $this->dispatch('$refresh');
                }),
        ];

        // Add download buttons for prepared batches
        $objectStreamBatches = Cache::get(self::BATCHES_CACHE_KEY . '_object_streams', []);
        if (!empty($objectStreamBatches)) {
            foreach ($objectStreamBatches as $index => $batch) {
                $batchNum = $index + 1;
                $batchSize = 0;
                foreach ($batch as $item) {
                    $batchSize += $item['file_size'] ?? 0;
                }
                
                $actions[] = Action::make('download_object_streams_batch_' . $batchNum)
                    ->label(sprintf('📥 Object Streams Batch %d/%d (%.1f MB)', 
                        $batchNum, 
                        count($objectStreamBatches), 
                        $batchSize / 1024 / 1024
                    ))
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () use ($batch, $batchNum, $objectStreamBatches) {
                        return $this->downloadSingleBatch($batch, $batchNum, count($objectStreamBatches), 'object-streams', 'object_streams');
                    });
            }
        }

        $allIssuesBatches = Cache::get(self::BATCHES_CACHE_KEY . '_all_issues', []);
        if (!empty($allIssuesBatches)) {
            foreach ($allIssuesBatches as $index => $batch) {
                $batchNum = $index + 1;
                $batchSize = 0;
                foreach ($batch as $item) {
                    $batchSize += $item['file_size'] ?? 0;
                }
                
                $actions[] = Action::make('download_all_issues_batch_' . $batchNum)
                    ->label(sprintf('📥 All Problems Batch %d/%d (%.1f MB)', 
                        $batchNum, 
                        count($allIssuesBatches), 
                        $batchSize / 1024 / 1024
                    ))
                    ->color('info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () use ($batch, $batchNum, $allIssuesBatches) {
                        return $this->downloadSingleBatch($batch, $batchNum, count($allIssuesBatches), 'all-problems', 'all_issues');
                    });
            }
        }

        return $actions;
    }

    /**
     * Download a single batch as ZIP file
     */
    protected function downloadSingleBatch(array $batch, int $batchNum, int $totalBatches, string $prefix, string $type)
    {
        $zipFilename = sprintf('%s-batch-%02d-of-%02d-%s.zip', $prefix, $batchNum, $totalBatches, date('Y-m-d'));
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

        // Add batch README
        $batchReadme = $this->generateBatchReadme($batch, $batchNum, $totalBatches);
        $zip->addFromString('README.txt', $batchReadme);

        // Add CSV file list
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
