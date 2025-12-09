@php
    $record = $getRecord();
    $books = $record->created_log ?? [];

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
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-sm font-medium text-green-800 dark:text-green-200">Total Created</div>
                <div class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">{{ $totalBooks }}</div>
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
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($books as $book)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
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
                            <td class="px-6 py-4 text-sm">
                                @if(!empty($book['book_id']))
                                    <a href="{{ route('filament.admin.resources.books.edit', ['record' => $book['book_id']]) }}"
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        View Book
                                        <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Download Report Button --}}
        <div class="mt-4 flex justify-end">
            <button
                onclick="downloadCreatedReport()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Created Report (CSV)
            </button>
        </div>

        <script>
            function downloadCreatedReport() {
                const books = @json($books);

                // Create CSV content
                let csv = 'Row Number,Title,Internal ID,PALM Code,Book ID,Timestamp\n';

                books.forEach(book => {
                    const row = book.row || 'Unknown';
                    const title = (book.title || 'Unknown').replace(/"/g, '""');
                    const internalId = book.internal_id || '';
                    const palmCode = book.palm_code || '';
                    const bookId = book.book_id || '';
                    const timestamp = book.timestamp || '';

                    csv += `"${row}","${title}","${internalId}","${palmCode}","${bookId}","${timestamp}"\n`;
                });

                // Create download link
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);

                link.setAttribute('href', url);
                link.setAttribute('download', 'import-created-{{ $record->id }}.csv');
                link.style.visibility = 'hidden';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="mt-2 text-sm font-medium">No new books were created during this import.</p>
    </div>
@endif
