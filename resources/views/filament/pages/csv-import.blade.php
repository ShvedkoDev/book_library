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

    {{-- Process Relationships Modal --}}
    <x-filament::modal id="process-relationships-modal" width="lg">
        <x-slot name="heading">
            Process Book Relationships
        </x-slot>

        <x-slot name="description">
            The CSV import is complete, but book relationships have not been processed yet. 
            Click the button below to match and link related books based on their relationship codes.
        </x-slot>

        <div class="space-y-4">
            <div class="bg-warning-50 dark:bg-warning-900/20 p-4 rounded-lg border border-warning-200 dark:border-warning-800">
                <p class="text-sm text-warning-800 dark:text-warning-200">
                    <strong>Note:</strong> This process may take a few minutes depending on the number of books imported. 
                    The page will show a notification when complete.
                </p>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="warning"
                wire:click="processRelationships"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Process Relationships Now</span>
                <span wire:loading>Processing...</span>
            </x-filament::button>

            <x-filament::button
                color="gray"
                x-on:click="close"
            >
                Skip for Now
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
