<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Book Summary Card --}}
        <x-filament::section>
            <x-slot name="heading">
                Book Information
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Views</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ $book->view_count }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Downloads</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ $book->download_count }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Publication Year</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ $book->publication_year ?? 'N/A' }}
                    </dd>
                </div>
            </div>
        </x-filament::section>

        {{-- Views Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
