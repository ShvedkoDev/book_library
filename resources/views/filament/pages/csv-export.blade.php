<x-filament-panels::page>
    <x-filament-panels::form wire:submit="export">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
            :full-width="false"
        />
    </x-filament-panels::form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Quick Help
        </x-slot>

        <div class="prose dark:prose-invert max-w-none">
            <h4>Export Process:</h4>
            <ol>
                <li>Select the export format (CSV or TSV)</li>
                <li>Configure export options (BOM, mapping row)</li>
                <li>Optionally apply filters to export specific books</li>
                <li>Click "Export CSV" to generate the file</li>
                <li>Download the file using the notification link</li>
            </ol>

            <h4>Export Formats:</h4>
            <ul>
                <li><strong>CSV:</strong> Standard comma-separated format with UTF-8 BOM for Excel</li>
                <li><strong>TSV:</strong> Tab-separated format, better for texts with commas</li>
            </ul>

            <h4>Filters:</h4>
            <p>Use filters to export only specific books. Leave filters empty to export all books.</p>
            <ul>
                <li><strong>Collection:</strong> Export books from a specific collection</li>
                <li><strong>Language:</strong> Export books in a specific language</li>
                <li><strong>Access Level:</strong> Filter by full, limited, or unavailable access</li>
                <li><strong>Date Range:</strong> Export books created within a date range</li>
                <li><strong>Publication Year:</strong> Filter by publication year range</li>
                <li><strong>Status:</strong> Export only active or inactive books</li>
                <li><strong>Featured:</strong> Export only featured books</li>
            </ul>

            <h4>Use Cases:</h4>
            <ul>
                <li><strong>Backup:</strong> Export all books for backup purposes</li>
                <li><strong>Bulk Edit:</strong> Export, edit in Excel/Calc, then re-import</li>
                <li><strong>Data Analysis:</strong> Export specific datasets for analysis</li>
                <li><strong>Sharing:</strong> Export filtered books for sharing with partners</li>
            </ul>
        </div>
    </x-filament::section>
</x-filament-panels::page>
