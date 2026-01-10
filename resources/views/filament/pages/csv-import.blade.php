<x-filament-panels::page>
    {{-- Loading indicator for CSV validation --}}
    <div wire:loading wire:target="validate" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Validating CSV...</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Checking file format and data. Please wait...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading indicator for CSV import --}}
    <div wire:loading wire:target="import" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Importing CSV...</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Processing books. Please don't close this page.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading indicator for relationship processing --}}
    <div wire:loading wire:target="processRelationships" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Processing Relationships...</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">This may take several minutes. Please don't close this page.</p>
                </div>
            </div>
        </div>
    </div>

    <x-filament-panels::form wire:submit="import">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
            :full-width="false"
        />
    </x-filament-panels::form>

    @if($data['csv_file'] ?? false)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Selected File
            </x-slot>

            <div class="space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">File:</span> {{ is_array($data['csv_file']) ? basename($data['csv_file'][0] ?? '') : basename($data['csv_file']) }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">Mode:</span> {{ ucfirst(str_replace('_', ' ', $data['mode'] ?? 'upsert')) }}
                </p>
            </div>
        </x-filament::section>
    @endif

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Quick Help
        </x-slot>

        <div class="prose dark:prose-invert max-w-none">
            <h4>Import Process:</h4>
            <ol>
                <li>Download a template (blank or with examples) from above</li>
                <li>Fill in your book data following the template format</li>
                <li>Upload the CSV file using the form above</li>
                <li>Click "Validate Only" to check for errors (recommended)</li>
                <li>Click "Import CSV" to perform the actual import</li>
                <li>Click "Process Relationships" button (above) to link related books and create translation relationships</li>
            </ol>

            <h4>Import Modes:</h4>
            <ul>
                <li><strong>Upsert:</strong> Creates new books and updates existing ones (recommended)</li>
                <li><strong>Create Only:</strong> Only creates new books, skips existing ones</li>
                <li><strong>Update Only:</strong> Only updates existing books, skips new ones</li>
                <li><strong>Create Duplicates:</strong> Creates all books as new, even duplicates</li>
            </ul>

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                For detailed field documentation, see the <a href="/docs/CSV_FIELD_MAPPING.md" target="_blank" class="text-primary-600 hover:underline">CSV Field Mapping Guide</a>.
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
