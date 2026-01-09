@php
    $record = $getRecord();
    $errorLog = $record->error_log;
@endphp

<div class="space-y-4">
    @if(empty($errorLog) || !is_array($errorLog))
        <p class="text-sm text-gray-500">No errors occurred.</p>
    @else
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                Total Errors: <span class="text-xl font-bold">{{ count($errorLog) }}</span>
            </p>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Row</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
                    @foreach($errorLog as $error)
                        <tr>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 rounded-full">{{ is_array($error) ? ($error['row'] ?? $error[0] ?? 'N/A') : 'N/A' }}</span></td>
                            <td class="px-6 py-4 text-sm">{{ is_string($error) ? $error : (is_array($error) ? ($error['message'] ?? json_encode($error)) : json_encode($error)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
