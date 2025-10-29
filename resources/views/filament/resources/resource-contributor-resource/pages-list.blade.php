<div class="space-y-4">
    @if($pages->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No pages found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                This contributor is not associated with any pages yet.
            </p>
        </div>
    @else
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            This contributor is associated with {{ $pages->count() }} page(s):
        </div>

        <div class="space-y-2">
            @foreach($pages as $page)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $page->title }}
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300">
                                {{ $page->slug }}
                            </code>
                            @if($page->is_published)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    Draft
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($page->is_published)
                        <a href="{{ $page->getUrl() }}" target="_blank" class="ml-3 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
