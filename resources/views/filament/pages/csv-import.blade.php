<x-filament-panels::page>
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
