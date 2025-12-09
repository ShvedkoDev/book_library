@php
    $record = $getRecord();
    $books = $record->skipped_log ?? [];

    if (!is_array($books)) {
        $books = [];
    }

    // Sort by row number
    usort($books, function($a, $b) {
        return ($a['row'] ?? 0) - ($b['row'] ?? 0);
    });

    $totalBooks = count($books);
@endphp

@if($totalBooks > 0)
    <div class="space-y-4">
        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Total Skipped</div>
                <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">{{ $totalBooks }}</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">First Row</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">Row {{ $books[0]['row'] ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">Last Row</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">Row {{ $books[count($books) - 1]['row'] ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Books Table --}}
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Row #
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Book Title
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            IDs
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Reason
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($books as $book)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    Row {{ $book['row'] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $book['title'] ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono space-y-1">
                                    @if(!empty($book['internal_id']))
                                        <div>Internal: {{ $book['internal_id'] }}</div>
                                    @endif
                                    @if(!empty($book['palm_code']))
                                        <div>PALM: {{ $book['palm_code'] }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    {{ $book['reason'] ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Download Report Button --}}
        <div class="mt-4 flex justify-end">
            <button
                onclick="downloadSkippedReport()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Skipped Report (CSV)
            </button>
        </div>

        <script>
            function downloadSkippedReport() {
                const books = @json($books);

                // Create CSV content
                let csv = 'Row Number,Title,Internal ID,PALM Code,Reason,Timestamp\n';

                books.forEach(book => {
                    const row = book.row || 'Unknown';
                    const title = (book.title || 'Unknown').replace(/"/g, '""');
                    const internalId = book.internal_id || '';
                    const palmCode = book.palm_code || '';
                    const reason = (book.reason || 'N/A').replace(/"/g, '""');
                    const timestamp = book.timestamp || '';

                    csv += `"${row}","${title}","${internalId}","${palmCode}","${reason}","${timestamp}"\n`;
                });

                // Create download link
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);

                link.setAttribute('href', url);
                link.setAttribute('download', 'import-skipped-{{ $record->id }}.csv');
                link.style.visibility = 'hidden';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm font-medium">No books were skipped during this import.</p>
    </div>
@endif
