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

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                // Initialize Tabulator table with remote data
                let table = new window.Tabulator("#bulk-edit-table", {
                    height: "600px",
                    layout: "fitColumns",
                    placeholder: "No books available",

                    // Enable remote pagination
                    pagination: true,
                    paginationMode: "remote",
                    paginationSize: 50,
                    paginationSizeSelector: [25, 50, 100, 200],

                    // Ajax configuration for remote data
                    ajaxURL: "/api/admin/bulk-editing/books",
                    ajaxConfig: {
                        method: "GET",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json",
                        },
                        credentials: "same-origin",
                    },

                    // Map pagination parameters to Laravel format
                    ajaxURLGenerator: function(url, config, params) {
                        url += "?page=" + params.page;
                        url += "&size=" + params.size;
                        return url;
                    },

                    // Handle response from server
                    ajaxResponse: function(url, params, response) {
                        console.log('API Response:', response);
                        return {
                            last_page: response.last_page,
                            data: response.data,
                        };
                    },

                    // Loading messages
                    ajaxLoader: true,
                    ajaxLoaderLoading: "<div class='p-4 text-center text-gray-600'>Loading books...</div>",
                    ajaxLoaderError: "<div class='p-4 text-center text-red-500'>Error loading data. Please refresh.</div>",

                    // Table columns
                    columns: [
                        {title: "ID", field: "id", width: 80, headerSort: false},
                        {title: "Title", field: "title", width: 300, headerSort: false},
                        {title: "Publisher", field: "publisher.name", width: 200, headerSort: false,
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },
                        {title: "Collection", field: "collection.name", width: 200, headerSort: false,
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },
                        {title: "Year", field: "publication_year", width: 100, headerSort: false},
                        {title: "Access", field: "access_level", width: 120, headerSort: false,
                            formatter: function(cell) {
                                const value = cell.getValue();
                                const badges = {
                                    full: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Full</span>',
                                    limited: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Limited</span>',
                                    unavailable: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Unavailable</span>',
                                };
                                return badges[value] || value;
                            }
                        },
                        {title: "Featured", field: "is_featured", width: 100, headerSort: false,
                            hozAlign: "center", formatter: "tickCross"
                        },
                        {title: "Active", field: "is_active", width: 100, headerSort: false,
                            hozAlign: "center", formatter: "tickCross"
                        },
                    ],
                });

                // Update status message when data loads
                table.on("dataLoaded", function(data) {
                    document.getElementById('status-message').textContent = 'Loaded ' + data.length + ' books';
                    console.log('Data loaded:', data.length, 'books');
                });

                // Handle load errors
                table.on("dataLoadError", function(error) {
                    console.error('Error loading data:', error);
                    document.getElementById('status-message').textContent = 'Error loading data';
                });

                console.log('Tabulator table initialized with remote data');
            });
        </script>
    @endpush
</x-filament-panels::page>
