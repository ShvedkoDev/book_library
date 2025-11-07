<div class="rounded-lg bg-blue-50 dark:bg-blue-950 p-4 border border-blue-200 dark:border-blue-800">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
                📋 This book is a duplicate
            </h3>
            <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                @if($sourceBook)
                    <p>
                        <span class="font-medium">Duplicated from:</span>
                        <a href="{{ \App\Filament\Resources\BookResource::getUrl('edit', ['record' => $sourceBook->id]) }}"
                           class="text-blue-700 dark:text-blue-300 hover:underline font-medium">
                            "{{ $sourceBook->title }}"
                        </a>
                    </p>
                @endif

                @if($duplicatedAt)
                    <p>
                        <span class="font-medium">Duplicated on:</span>
                        {{ $duplicatedAt->format('F j, Y \a\t g:i A') }}
                        <span class="text-blue-600 dark:text-blue-400">({{ $duplicatedAt->diffForHumans() }})</span>
                    </p>
                @endif

                <p class="text-xs italic text-blue-700 dark:text-blue-300 mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
                    ℹ️ All relationships and classifications were copied from the original book. Please review all fields to ensure accuracy.
                </p>
            </div>
        </div>
    </div>
</div>
