<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info Card --}}
        <x-filament::section>
            <x-slot name="heading">
                About PDF Compression Check
            </x-slot>

            <x-slot name="description">
                This tool checks all PDF files in your library to determine if they can have cover pages added. It specifically identifies PDFs using Object Streams (PDF 1.5+) which require conversion or paid parser.
            </x-slot>

            <div class="space-y-3 text-sm">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Normal PDFs:</strong> Can have covers added successfully. These work perfectly.
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 text-danger-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Object Streams (PDF 1.5+):</strong> Most common issue (~70% of PDFs). These use advanced compression that requires either:
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li>Batch conversion to PDF 1.4 using Ghostscript</li>
                            <li>Purchasing the paid FPDI PDF-Parser (~€150-200)</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Other Compression Issues:</strong> Rare compression filters that need investigation.
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    <x-heroicon-o-x-circle class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Error PDFs:</strong> Cannot be read by FPDI. May be corrupted or use unsupported PDF features.
                    </div>
                </div>

                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold mb-2">How to Fix Object Streams PDFs (PDF 1.5+):</p>
                    <p class="text-sm mb-2 text-gray-600 dark:text-gray-400">Convert PDF 1.5/1.6 → PDF 1.4 using Ghostscript (removes Object Streams):</p>
                    <pre class="bg-gray-200 dark:bg-gray-700 p-2 rounded text-xs overflow-x-auto"><code>gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 \
   -dNOPAUSE -dQUIET -dBATCH \
   -sOutputFile=output.pdf input.pdf</code></pre>
                    <p class="text-sm mt-2 text-gray-600 dark:text-gray-400">After conversion, re-upload the files and recheck.</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Batch Download Buttons Section --}}
        @php
            $objectStreamBatches = Cache::get('pdf_export_batches_object_streams', []);
            $allIssuesBatches = Cache::get('pdf_export_batches_all_issues', []);
            $hasBatches = !empty($objectStreamBatches) || !empty($allIssuesBatches);
        @endphp

        @if($hasBatches)
        <x-filament::section>
            <x-slot name="heading">
                📥 Prepared Batch Downloads
            </x-slot>

            <x-slot name="description">
                Click individual batch buttons below to download. Each batch contains up to 100MB of PDFs to avoid timeout errors.
            </x-slot>

            <div class="space-y-6">
                @if(!empty($objectStreamBatches))
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">
                        Object Streams PDFs ({{ count($objectStreamBatches) }} batches)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($objectStreamBatches as $index => $batch)
                            @php
                                $batchNum = $index + 1;
                                $batchSize = 0;
                                foreach ($batch as $item) {
                                    $batchSize += $item['file_size'] ?? 0;
                                }
                                $batchSizeMB = number_format($batchSize / 1024 / 1024, 1);
                                $fileCount = count($batch);
                            @endphp
                            <a 
                                href="{{ route('admin.pdf-compression-check.download-batch', [
                                    'type' => 'object_streams',
                                    'batch' => $batchNum
                                ]) }}"
                                class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                            >
                                <div class="flex-1">
                                    <div class="font-semibold text-green-700 dark:text-green-400">
                                        Batch {{ $batchNum }}/{{ count($objectStreamBatches) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $fileCount }} files • {{ $batchSizeMB }} MB
                                    </div>
                                </div>
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 ml-2"/>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($allIssuesBatches))
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">
                        All Problem PDFs ({{ count($allIssuesBatches) }} batches)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($allIssuesBatches as $index => $batch)
                            @php
                                $batchNum = $index + 1;
                                $batchSize = 0;
                                foreach ($batch as $item) {
                                    $batchSize += $item['file_size'] ?? 0;
                                }
                                $batchSizeMB = number_format($batchSize / 1024 / 1024, 1);
                                $fileCount = count($batch);
                            @endphp
                            <a 
                                href="{{ route('admin.pdf-compression-check.download-batch', [
                                    'type' => 'all_issues',
                                    'batch' => $batchNum
                                ]) }}"
                                class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                            >
                                <div class="flex-1">
                                    <div class="font-semibold text-blue-700 dark:text-blue-400">
                                        Batch {{ $batchNum }}/{{ count($allIssuesBatches) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $fileCount }} files • {{ $batchSizeMB }} MB
                                    </div>
                                </div>
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 ml-2"/>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-start space-x-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"/>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Note:</strong> Batch downloads are cached for 1 hour. If buttons disappear, click "Prepare..." button again to regenerate them.
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
        @endif

        {{-- Statistics Card --}}
        <x-filament::section>
            <x-slot name="heading">
                Library-wide PDF Status
            </x-slot>

            @php
                $stats = $this->pdfStatistics;
                $total = max(1, $stats['total'] ?? 0);
                $statusCards = [
                    'normal' => ['label' => 'Normal (Can add cover)', 'color' => 'green'],
                    'object_streams' => ['label' => 'Object Streams (PDF 1.5+)', 'color' => 'red'],
                    'compressed' => ['label' => 'Other Compression', 'color' => 'orange'],
                    'error' => ['label' => 'Error (Unreadable)', 'color' => 'yellow'],
                    'missing' => ['label' => 'Missing File', 'color' => 'gray'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['total']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total PDF Files</div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Updated {{ optional($stats['last_updated'])->diffForHumans() }}</p>
                </div>

                @foreach ($statusCards as $key => $card)
                    <div class="p-4 bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">
                            {{ number_format($stats[$key] ?? 0) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $card['label'] }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format((($stats[$key] ?? 0) / $total) * 100, 1) }}%
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200 mb-2">Summary</h4>
                    <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <dt>Can add covers now</dt>
                            <dd class="font-semibold text-green-600 dark:text-green-400">{{ number_format($stats['normal'] ?? 0) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Object Streams (PDF 1.5+)</dt>
                            <dd class="font-semibold text-red-600 dark:text-red-400">{{ number_format($stats['object_streams'] ?? 0) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Other compression issues</dt>
                            <dd class="font-semibold text-orange-600 dark:text-orange-400">{{ number_format($stats['compressed'] ?? 0) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Need investigation</dt>
                            <dd class="font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format(($stats['error'] ?? 0) + ($stats['missing'] ?? 0)) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200 mb-2">Next Actions</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                        <li>Use the table below to filter and export problematic files.</li>
                        <li>Click "Export Compressed List" to download the full list for processing.</li>
                        <li>After fixing files, use “Clear Cache & Recheck All” to refresh these totals.</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- Table --}}
        <x-filament::section>
            <x-slot name="heading">
                All PDF Files
            </x-slot>

            <x-slot name="description">
                Complete list of all PDF files with their compression status. Click "Recheck" to refresh individual files.
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
