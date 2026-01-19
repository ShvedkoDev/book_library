<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info Card --}}
        <x-filament::section>
            <x-slot name="heading">
                About PDF Compression Check
            </x-slot>

            <x-slot name="description">
                This tool checks all PDF files in your library to determine if they can have cover pages added.
            </x-slot>

            <div class="space-y-3 text-sm">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Normal PDFs:</strong> Can have covers added successfully. These work perfectly.
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Compressed PDFs:</strong> Cannot have covers added when <code>exec()</code> is disabled on the server. These PDFs need to be decompressed locally and re-uploaded, or the server needs <code>exec()</code> enabled.
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    <x-heroicon-o-x-circle class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5"/>
                    <div>
                        <strong>Error PDFs:</strong> Cannot be read by FPDI. May be corrupted or use unsupported PDF features.
                    </div>
                </div>

                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold mb-2">How to Fix Compressed PDFs:</p>
                    <ol class="list-decimal list-inside space-y-1 text-gray-700 dark:text-gray-300">
                        <li>Export the list using the "Export Compressed List" button above</li>
                        <li>Download the problematic PDF files</li>
                        <li>Decompress them locally using: <code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">qpdf --stream-data=uncompress input.pdf output.pdf</code></li>
                        <li>Re-upload the decompressed versions</li>
                        <li>Recheck to verify they now show as "Normal"</li>
                    </ol>
                </div>
            </div>
        </x-filament::section>

        {{-- Statistics Card --}}
        <x-filament::section>
            <x-slot name="heading">
                Quick Statistics
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $totalPdfs = \App\Models\BookFile::where('file_type', 'pdf')->count();
                    $stats = [
                        'total' => $totalPdfs,
                        'normal' => 0,
                        'compressed' => 0,
                        'error' => 0,
                    ];

                    // Quick sample check (first 50 to give an idea)
                    \App\Models\BookFile::where('file_type', 'pdf')->limit(50)->each(function($file) use (&$stats) {
                        $filePath = storage_path('app/public/' . $file->file_path);
                        $result = \App\Filament\Pages\PdfCompressionCheck::checkPdfCompression($filePath);
                        if (isset($stats[$result['status']])) {
                            $stats[$result['status']]++;
                        }
                    });
                @endphp

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total PDF Files</div>
                </div>

                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['normal'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Normal (Sample of 50)</div>
                </div>

                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['compressed'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Compressed (Sample of 50)</div>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['error'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Errors (Sample of 50)</div>
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                Note: Statistics are based on a sample. Use the table below to check all files.
            </p>
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
