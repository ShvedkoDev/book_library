<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Form --}}
        <div class="filament-forms-component-wrapper">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6 flex gap-4">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-arrow-up-tray">
                        Upload Files
                    </x-filament::button>

                    <x-filament::button type="button" color="gray" size="lg" wire:click="$refresh" icon="heroicon-o-arrow-path">
                        Refresh List
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Information Alert --}}
        <x-filament::section>
            <x-slot name="heading">
                Important Information
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Original filenames are preserved:</strong> Files are saved exactly as named when uploaded.</li>
                    <li><strong>Storage location:</strong> All files (PDFs and thumbnails) are stored in <code>storage/app/public/books/</code></li>
                    <li><strong>Matching strategy:</strong> During CSV import, the system will match filenames exactly as they appear in the CSV.</li>
                    <li><strong>Recommended workflow:</strong>
                        <ol class="list-decimal pl-5 mt-2">
                            <li>Upload all PDF files using the PDF upload zone above</li>
                            <li>Upload all thumbnail images using the thumbnail upload zone above</li>
                            <li>Go to CSV Import and upload your book catalog CSV file</li>
                            <li>The system will automatically match and link files to book records</li>
                        </ol>
                    </li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Files Table --}}
        <div class="filament-tables-component-wrapper">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
