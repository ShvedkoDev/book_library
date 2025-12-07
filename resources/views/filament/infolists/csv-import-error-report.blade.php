@php
    $record = $getRecord();
    $errors = $record->error_log ?? [];

    if (!is_array($errors)) {
        $errors = [];
    }

    // Sort errors by row number
    usort($errors, function($a, $b) {
        return ($a['row'] ?? 0) - ($b['row'] ?? 0);
    });

    $totalErrors = count($errors);
@endphp

@if($totalErrors > 0)
    <div class="space-y-4">
        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="text-sm font-medium text-red-800 dark:text-red-200">Total Failed</div>
                <div class="text-2xl font-bold text-red-900 dark:text-red-100 mt-1">{{ $totalErrors }}</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">First Failure</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">Row {{ $errors[0]['row'] ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">Last Failure</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">Row {{ $errors[count($errors) - 1]['row'] ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Error Table --}}
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Row #
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Book Info
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Error Reason
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Column
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($errors as $error)
                        @php
                            $message = $error['message'] ?? 'Unknown error';

                            // Extract book info from message (format: "error | Book: \"Title\" (ID: id)")
                            $bookTitle = 'Unknown';
                            $bookId = 'Unknown';
                            $errorReason = $message;

                            if (preg_match('/(.+?)\s*\|\s*Book:\s*"(.+?)"\s*\(ID:\s*(.+?)\)/', $message, $matches)) {
                                $errorReason = trim($matches[1]);
                                $bookTitle = $matches[2];
                                $bookId = $matches[3];
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                    Row {{ $error['row'] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $bookTitle }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">ID: {{ $bookId }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div class="max-w-2xl">
                                    {{ $errorReason }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                    {{ $error['column'] ?? 'N/A' }}
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
                onclick="downloadErrorReport()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Error Report (CSV)
            </button>
        </div>

        <script>
            function downloadErrorReport() {
                const errors = @json($errors);

                // Create CSV content
                let csv = 'Row Number,Column,Error Message,Timestamp\n';

                errors.forEach(error => {
                    const row = error.row || 'Unknown';
                    const column = error.column || 'N/A';
                    const message = (error.message || 'Unknown error').replace(/"/g, '""');
                    const timestamp = error.timestamp || '';

                    csv += `"${row}","${column}","${message}","${timestamp}"\n`;
                });

                // Create download link
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);

                link.setAttribute('href', url);
                link.setAttribute('download', 'import-errors-{{ $record->id }}.csv');
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
        <p class="mt-2 text-sm font-medium">No errors - all books imported successfully!</p>
    </div>
@endif
