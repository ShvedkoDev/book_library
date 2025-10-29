<div class="space-y-4">
    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Found {{ count($sections) }} section(s) in this page content:
    </div>

    <div class="space-y-3">
        @foreach ($sections as $section)
            <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 text-sm font-medium">
                        {{ $loop->iteration }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $section['heading'] }}
                    </div>
                    <div class="mt-1 flex items-center gap-2">
                        <code class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300">
                            #{{ $section['anchor'] }}
                        </code>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
