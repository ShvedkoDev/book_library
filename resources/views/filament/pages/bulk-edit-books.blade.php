<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex gap-2 flex-wrap">
                {{-- Filters will go here --}}
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Filters will be added in next phase
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                {{-- Actions: Save, Export, Import --}}
                <button id="save-changes-btn" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                    <span id="save-icon">💾</span>
                    <span>Save Changes (<span id="save-count">0</span>)</span>
                </button>
            </div>
        </div>

        {{-- Tabulator Container --}}
        <div id="bulk-edit-table" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"></div>

        {{-- Status Bar --}}
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div class="flex items-center gap-2">
                <span>Unsaved changes:</span>
                <span id="edit-count" class="font-bold text-orange-600">0 changes</span>
            </div>
            <div id="status-message" class="text-gray-500"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Tabulator is ready:', typeof window.Tabulator !== 'undefined');

                if (typeof window.Tabulator === 'undefined') {
                    console.error('Tabulator not loaded! Check your asset compilation.');
                    document.getElementById('status-message').textContent = 'Error: Tabulator not loaded';
                    return;
                }

                // Initialize Tabulator table (basic setup for now)
                let table = new window.Tabulator("#bulk-edit-table", {
                    height: "500px",
                    layout: "fitColumns",
                    placeholder: "Loading books...",
                    columns: [
                        {title: "ID", field: "id", width: 80},
                        {title: "Title", field: "title", width: 300},
                        {title: "Year", field: "publication_year", width: 100},
                        {title: "Status", field: "is_active", width: 100, formatter: "tickCross"},
                    ],
                    data: [
                        {id: 1, title: "Sample Book 1", publication_year: 2023, is_active: true},
                        {id: 2, title: "Sample Book 2", publication_year: 2024, is_active: true},
                        {id: 3, title: "Sample Book 3", publication_year: 2025, is_active: false},
                    ],
                });

                document.getElementById('status-message').textContent = 'Tabulator initialized successfully ✓';
                console.log('Tabulator table initialized:', table);
            });
        </script>
    @endpush
</x-filament-panels::page>
