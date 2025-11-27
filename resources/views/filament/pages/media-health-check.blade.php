<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Media Health Check</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Scan your library to find books with missing media files and orphaned files in storage.
                </p>
            </div>

            @if(!$scanCompleted)
                <x-filament::button wire:click="startScan" color="primary" icon="heroicon-o-magnifying-glass">
                    Start Scan
                </x-filament::button>
            @else
                <div class="flex gap-2">
                    <x-filament::button wire:click="exportResults" color="success" icon="heroicon-o-arrow-down-tray">
                        Export Results
                    </x-filament::button>
                    <x-filament::button wire:click="startScan" color="primary" icon="heroicon-o-arrow-path">
                        Re-scan
                    </x-filament::button>
                </div>
            @endif
        </div>

        @if($scanCompleted)
            {{-- Statistics Dashboard --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Books</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_books']) }}</p>
                        </div>
                        <x-heroicon-o-book-open class="h-8 w-8 text-blue-500" />
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Books Without PDF</p>
                            <p class="mt-2 text-3xl font-semibold {{ $stats['books_without_pdf'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($stats['books_without_pdf']) }}
                            </p>
                        </div>
                        <x-heroicon-o-document-text class="h-8 w-8 {{ $stats['books_without_pdf'] > 0 ? 'text-red-500' : 'text-green-500' }}" />
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Books Without Cover</p>
                            <p class="mt-2 text-3xl font-semibold {{ $stats['books_without_cover'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ number_format($stats['books_without_cover']) }}
                            </p>
                        </div>
                        <x-heroicon-o-photo class="h-8 w-8 {{ $stats['books_without_cover'] > 0 ? 'text-orange-500' : 'text-green-500' }}" />
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Orphaned Files</p>
                            <p class="mt-2 text-3xl font-semibold {{ $stats['orphaned_files'] > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ number_format($stats['orphaned_files']) }}
                            </p>
                        </div>
                        <x-heroicon-o-folder-open class="h-8 w-8 {{ $stats['orphaned_files'] > 0 ? 'text-yellow-500' : 'text-green-500' }}" />
                    </div>
                </div>
            </div>

            {{-- Additional Stats Row --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Missing PDF Files in Storage</p>
                    <p class="mt-1 text-2xl font-semibold {{ $stats['books_with_missing_pdf_files'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($stats['books_with_missing_pdf_files']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Missing Cover Files in Storage</p>
                    <p class="mt-1 text-2xl font-semibold {{ $stats['books_with_missing_cover_files'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                        {{ number_format($stats['books_with_missing_cover_files']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Files in Storage</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['total_files_in_storage']) }}
                    </p>
                </div>
            </div>

            {{-- Books Without PDF --}}
            @if(count($booksWithoutPdf) > 0)
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Books Without PDF ({{ count($booksWithoutPdf) }})
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">These books have no PDF files attached in the database.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Internal ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">PALM Code</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach($booksWithoutPdf as $book)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['id'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['title'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['internal_id'] ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['palm_code'] ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('filament.admin.resources.books.edit', $book['id']) }}" target="_blank" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                <x-filament::button size="sm" color="primary" icon="heroicon-o-pencil">
                                                    Edit
                                                </x-filament::button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Books Without Cover --}}
            @if(count($booksWithoutCover) > 0)
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Books Without Cover ({{ count($booksWithoutCover) }})
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">These books have no cover/thumbnail files attached in the database.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Internal ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">PALM Code</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach($booksWithoutCover as $book)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['id'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['title'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['internal_id'] ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['palm_code'] ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('filament.admin.resources.books.edit', $book['id']) }}" target="_blank" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                <x-filament::button size="sm" color="primary" icon="heroicon-o-pencil">
                                                    Edit
                                                </x-filament::button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Books With Missing PDF Files --}}
            @if(count($booksWithMissingPdfFiles) > 0)
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Books With Missing PDF Files ({{ count($booksWithMissingPdfFiles) }})
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">These books have PDF records in the database, but the files don't exist in storage.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Book ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Filename</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">File Path</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach($booksWithMissingPdfFiles as $book)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['id'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['title'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['filename'] }}</td>
                                        <td class="px-6 py-4 text-sm font-mono text-xs text-red-600 dark:text-red-400">{{ $book['file_path'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('filament.admin.resources.books.edit', $book['id']) }}" target="_blank" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                <x-filament::button size="sm" color="primary" icon="heroicon-o-pencil">
                                                    Edit
                                                </x-filament::button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Books With Missing Cover Files --}}
            @if(count($booksWithMissingCoverFiles) > 0)
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Books With Missing Cover Files ({{ count($booksWithMissingCoverFiles) }})
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">These books have cover records in the database, but the files don't exist in storage.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Book ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Filename</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">File Path</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach($booksWithMissingCoverFiles as $book)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['id'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $book['title'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $book['filename'] }}</td>
                                        <td class="px-6 py-4 text-sm font-mono text-xs text-red-600 dark:text-red-400">{{ $book['file_path'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('filament.admin.resources.books.edit', $book['id']) }}" target="_blank" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                <x-filament::button size="sm" color="primary" icon="heroicon-o-pencil">
                                                    Edit
                                                </x-filament::button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Orphaned Files --}}
            @if(count($orphanedFiles) > 0)
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Orphaned Files ({{ count($orphanedFiles) }})
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">These files exist in storage but are not connected to any book in the database.</p>
                            </div>
                            @if(count($orphanedFiles) > 0)
                                <x-filament::button
                                    wire:click="deleteAllOrphanedFiles"
                                    color="danger"
                                    icon="heroicon-o-trash"
                                    wire:confirm="Are you sure you want to delete all {{ count($orphanedFiles) }} orphaned files? This action cannot be undone."
                                >
                                    Delete All Orphaned Files
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Filename</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Modified</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Path</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach($orphanedFiles as $file)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $file['filename'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                                {{ $file['type'] === 'PDF' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                {{ $file['type'] === 'Image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                                {{ $file['type'] === 'Other' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                                            ">
                                                {{ $file['type'] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $file['size'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $file['modified'] }}</td>
                                        <td class="px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $file['path'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <x-filament::button
                                                wire:click="deleteOrphanedFile('{{ $file['path'] }}')"
                                                color="danger"
                                                size="sm"
                                                icon="heroicon-o-trash"
                                                wire:confirm="Are you sure you want to delete this file? This action cannot be undone."
                                            >
                                                Delete
                                            </x-filament::button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Summary Message --}}
            @if(count($booksWithoutPdf) === 0 && count($booksWithoutCover) === 0 && count($booksWithMissingPdfFiles) === 0 && count($booksWithMissingCoverFiles) === 0 && count($orphanedFiles) === 0)
                <div class="rounded-lg bg-green-50 p-6 text-center dark:bg-green-900/20">
                    <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-500" />
                    <h3 class="mt-4 text-lg font-semibold text-green-900 dark:text-green-100">All Clear!</h3>
                    <p class="mt-2 text-sm text-green-700 dark:text-green-300">
                        No issues found. All books have their required media files, and there are no orphaned files in storage.
                    </p>
                </div>
            @endif
        @else
            {{-- Initial State --}}
            <div class="rounded-lg bg-gray-50 p-12 text-center dark:bg-gray-900">
                <x-heroicon-o-magnifying-glass class="mx-auto h-16 w-16 text-gray-400" />
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No Scan Results</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Click "Start Scan" to analyze your library and identify media issues.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
