<div class="space-y-4">
    @if($items->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No usage found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                This file is not currently being used.
            </p>
        </div>
    @else
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            This file is used by {{ $items->count() }} {{ $type === 'books' ? 'book(s)' : 'page(s)' }}:
        </div>

        <div class="space-y-2">
            @foreach($items as $item)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $item->title }}
                        </div>
                        @if($type === 'books' && isset($item->file_path))
                            <div class="mt-1">
                                <code class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300">
                                    {{ basename($item->file_path) }}
                                </code>
                            </div>
                        @endif
                    </div>
                    @if($type === 'books')
                        <a href="{{ route('filament.admin.resources.books.edit', ['record' => $item->id]) }}"
                           class="ml-3 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('filament.admin.resources.pages.edit', ['record' => $item->id]) }}"
                           class="ml-3 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
