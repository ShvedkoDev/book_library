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

                    BookFile::where('file_type', 'pdf')->with('book')->chunk(100, function ($files) use (&$objectStreams) {
                        foreach ($files as $file) {
                            $filePath = storage_path('app/public/' . $file->file_path);
                            $result = self::checkPdfCompression($filePath);

                            if ($result['status'] === 'object_streams') {
                                $objectStreams[] = [
                                    'id' => $file->id,
                                    'book_id' => $file->book_id,
                                    'book_title' => $file->book?->title,
                                    'filename' => $file->filename,
                                    'file_path' => $file->file_path,
                                    'pdf_version' => $result['pdf_version'] ?? 'Unknown',
                                    'message' => $result['message'],
                                ];
                            }
                        }
                    });

                    $csv = "ID,Book ID,Book Title,Filename,File Path,PDF Version,Status\n";
                    foreach ($objectStreams as $item) {
                        $csv .= sprintf(
                            '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                            $item['id'],
                            $item['book_id'],
                            str_replace('"', '""', $item['book_title'] ?? ''),
                            str_replace('"', '""', $item['filename']),
                            $item['file_path'],
                            $item['pdf_version'],
                            $item['message']
                        );
                    }

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'object-streams-pdfs-' . date('Y-m-d') . '.csv');
                }),

            Action::make('export_all_issues')
                ->label('Export All Problem PDFs')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->action(function () {
                    $problems = [];

                    BookFile::where('file_type', 'pdf')->with('book')->chunk(100, function ($files) use (&$problems) {
                        foreach ($files as $file) {
                            $filePath = storage_path('app/public/' . $file->file_path);
                            $result = self::checkPdfCompression($filePath);

                            if (in_array($result['status'], ['object_streams', 'compressed', 'error'])) {
                                $problems[] = [
                                    'id' => $file->id,
                                    'book_id' => $file->book_id,
                                    'book_title' => $file->book?->title,
                                    'filename' => $file->filename,
                                    'file_path' => $file->file_path,
                                    'status' => $result['status'],
                                    'pdf_version' => $result['pdf_version'] ?? 'Unknown',
                                    'message' => $result['message'],
                                ];
                            }
                        }
                    });

                    $csv = "ID,Book ID,Book Title,Filename,File Path,Issue Type,PDF Version,Details\n";
                    foreach ($problems as $item) {
                        $csv .= sprintf(
                            '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                            $item['id'],
                            $item['book_id'],
                            str_replace('"', '""', $item['book_title'] ?? ''),
                            str_replace('"', '""', $item['filename']),
                            $item['file_path'],
                            $item['status'],
                            $item['pdf_version'],
                            str_replace('"', '""', $item['message'])
                        );
                    }

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'all-problem-pdfs-' . date('Y-m-d') . '.csv');
                }),
        ];
    }
}
