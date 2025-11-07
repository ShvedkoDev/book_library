<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex gap-4 flex-wrap items-center">
                {{-- Keyboard Shortcuts Info --}}
                <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded">
                    💡 <strong>Tip:</strong>
                    <span class="ml-1">Shift+Click for range selection</span>
                    <span class="ml-2">Ctrl/Cmd+C to copy</span>
                    <span class="ml-2">Ctrl/Cmd+V to paste</span>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                {{-- Bulk Operations --}}
                <button id="bulk-update-btn" type="button" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                    <span>🔄</span>
                    <span>Bulk Update</span>
                </button>
                <button id="fill-down-btn" type="button" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                    <span>⬇️</span>
                    <span>Fill Down</span>
                </button>
                <button id="find-replace-btn" type="button" class="inline-flex items-center gap-2 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                    <span>🔍</span>
                    <span>Find & Replace</span>
                </button>

                {{-- Save Action --}}
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

    {{-- Bulk Update Modal --}}
    <div id="bulk-update-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4" id="modal-title">
                        Bulk Update Selected Rows
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <span id="selected-count">0</span> rows selected
                    </p>
                    <div class="space-y-4">
                        <div>
                            <label for="bulk-field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Field to Update:</label>
                            <select id="bulk-field" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Select Field --</option>
                                <option value="publisher_id">Publisher</option>
                                <option value="collection_id">Collection</option>
                                <option value="access_level">Access Level</option>
                                <option value="physical_type">Physical Type</option>
                                <option value="is_featured">Featured</option>
                                <option value="is_active">Active</option>
                            </select>
                        </div>
                        <div id="bulk-value-container">
                            <label for="bulk-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Value:</label>
                            <input type="text" id="bulk-value" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Enter new value" />
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button id="apply-bulk-update" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Apply
                    </button>
                    <button id="cancel-bulk-update" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Find & Replace Modal --}}
    <div id="find-replace-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                        Find & Replace
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label for="find-field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Field:</label>
                            <select id="find-field" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="title">Title</option>
                                <option value="subtitle">Subtitle</option>
                                <option value="translated_title">Translated Title</option>
                                <option value="description">Description</option>
                            </select>
                        </div>
                        <div>
                            <label for="find-text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Find:</label>
                            <input type="text" id="find-text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Text to find" />
                        </div>
                        <div>
                            <label for="replace-text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Replace with:</label>
                            <input type="text" id="replace-text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Replacement text" />
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="match-case" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                            <label for="match-case" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Match case</label>
                        </div>
                        <div id="find-results" class="text-sm text-gray-600 dark:text-gray-400 min-h-[20px]"></div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button id="replace-all-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Replace All
                    </button>
                    <button id="find-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Find
                    </button>
                    <button id="cancel-find-replace" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Error Styling --}}
    @push('styles')
        <style>
            /* Validation error styling */
            .tabulator-cell.tabulator-validation-fail {
                border: 2px solid #ef4444 !important;
                background-color: #fee2e2 !important;
            }

            .tabulator-cell.tabulator-validation-fail:hover::after {
                content: attr(data-validation-error);
                position: absolute;
                background: #ef4444;
                color: white;
                padding: 6px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                top: 100%;
                left: 0;
                white-space: nowrap;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                margin-top: 2px;
            }

            /* Changed row styling */
            .tabulator-row.row-changed {
                background-color: #fef3c7 !important;
            }

            .tabulator-row.row-changed:hover {
                background-color: #fde68a !important;
            }

            /* Selected row styling */
            .tabulator-row.tabulator-selected {
                background-color: #dbeafe !important;
            }

            .tabulator-row.tabulator-selected:hover {
                background-color: #bfdbfe !important;
            }
        </style>
    @endpush

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

                // Lookup data for dropdowns
                let publishers = [];
                let collections = [];
                let languages = [];
                let creators = [];

                // Load lookup data
                Promise.all([
                    fetch('/api/admin/publishers', {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    }).then(r => r.json()).then(data => { publishers = data; }),

                    fetch('/api/admin/collections', {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    }).then(r => r.json()).then(data => { collections = data; }),

                    fetch('/api/admin/languages', {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    }).then(r => r.json()).then(data => { languages = data; }),

                    fetch('/api/admin/creators', {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    }).then(r => r.json()).then(data => { creators = data; }),
                ]).then(() => {
                    console.log('Lookup data loaded:', {
                        publishers: publishers.length,
                        collections: collections.length,
                        languages: languages.length,
                        creators: creators.length,
                    });
                    initializeTable();
                }).catch(error => {
                    console.error('Error loading lookup data:', error);
                    document.getElementById('status-message').textContent = 'Error loading lookup data';
                });

                // Custom Validators
                var yearRangeValidator = function(cell, value, parameters) {
                    const currentYear = new Date().getFullYear();
                    const minYear = parameters.min || 1900;
                    const maxYear = parameters.max || currentYear;

                    if (!value) return true; // Allow empty if not required

                    const year = parseInt(value);
                    if (isNaN(year)) {
                        return `Must be a valid year`;
                    }
                    if (year < minYear || year > maxYear) {
                        return `Year must be between ${minYear} and ${maxYear}`;
                    }
                    return true;
                };

                var publisherExistsValidator = function(cell, value, parameters) {
                    if (!value) return true; // Allow empty
                    const exists = publishers.some(p => p.value === value);
                    return exists ? true : "Publisher does not exist";
                };

                var collectionExistsValidator = function(cell, value, parameters) {
                    if (!value) return true; // Allow empty
                    const exists = collections.some(c => c.value === value);
                    return exists ? true : "Collection does not exist";
                };

                // Batch validation function
                function validateAllChanges(table) {
                    const editedCells = table.getEditedCells();
                    const errors = [];

                    editedCells.forEach(cell => {
                        const valid = cell.validate();
                        if (valid !== true) {
                            errors.push({
                                row: cell.getRow().getPosition(),
                                column: cell.getColumn().getDefinition().title,
                                error: valid,
                            });
                        }
                    });

                    if (errors.length > 0) {
                        displayValidationErrors(errors);
                        return false;
                    }
                    return true;
                }

                function displayValidationErrors(errors) {
                    let errorText = 'Validation errors:\n';
                    errors.forEach(err => {
                        errorText += `\n• Row ${err.row}, ${err.column}: ${err.error}`;
                    });
                    alert(errorText);
                    document.getElementById('status-message').textContent = `${errors.length} validation error(s) found`;
                }

                function initializeTable() {
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

                    // Enable range selection
                    selectableRange: true,
                    selectableRangeMode: "click",

                    // Enable clipboard (copy/paste)
                    clipboard: true,
                    clipboardCopyRowRange: "range",
                    clipboardCopyConfig: {
                        rowHeaders: false,
                        columnHeaders: false,
                    },
                    clipboardCopyStyled: false,
                    clipboardPasteParser: "range",
                    clipboardPasteAction: "range",

                    // Enable row selection
                    selectable: true,
                    selectableRollingSelection: false,

                    // Table columns with editors
                    columns: [
                        // Row Selection Checkbox
                        {
                            formatter: "rowSelection",
                            titleFormatter: "rowSelection",
                            hozAlign: "center",
                            headerSort: false,
                            width: 50,
                            frozen: true,
                        },

                        // ID - Read only
                        {
                            title: "ID",
                            field: "id",
                            width: 80,
                            headerSort: false,
                        },

                        // Title - Text input with validation
                        {
                            title: "Title",
                            field: "title",
                            width: 300,
                            headerSort: false,
                            editor: "input",
                            editorParams: {
                                selectContents: true,
                                elementAttributes: {
                                    maxlength: "500",
                                },
                            },
                            validator: ["required", "minLength:3", "maxLength:500"],
                        },

                        // Subtitle - Text input
                        {
                            title: "Subtitle",
                            field: "subtitle",
                            width: 250,
                            headerSort: false,
                            editor: "input",
                            editorParams: {
                                selectContents: true,
                                elementAttributes: {
                                    maxlength: "500",
                                },
                            },
                            validator: "maxLength:500",
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },

                        // Translated Title - Text input
                        {
                            title: "Translated Title",
                            field: "translated_title",
                            width: 250,
                            headerSort: false,
                            editor: "input",
                            editorParams: {
                                selectContents: true,
                                elementAttributes: {
                                    maxlength: "500",
                                },
                            },
                            validator: "maxLength:500",
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },

                        // Description - Textarea
                        {
                            title: "Description",
                            field: "description",
                            width: 300,
                            headerSort: false,
                            editor: "textarea",
                            editorParams: {
                                elementAttributes: {
                                    rows: "4",
                                },
                                verticalNavigation: "editor",
                            },
                            formatter: "textarea",
                        },

                        // Publisher - Dropdown
                        {
                            title: "Publisher",
                            field: "publisher_id",
                            width: 200,
                            headerSort: false,
                            editor: "list",
                            editorParams: {
                                values: publishers,
                                autocomplete: true,
                                freetext: false,
                                allowEmpty: true,
                                listOnEmpty: true,
                            },
                            validator: publisherExistsValidator,
                            formatter: function(cell) {
                                const pub = publishers.find(p => p.value === cell.getValue());
                                return pub ? pub.label : '-';
                            }
                        },

                        // Collection - Dropdown
                        {
                            title: "Collection",
                            field: "collection_id",
                            width: 200,
                            headerSort: false,
                            editor: "list",
                            editorParams: {
                                values: collections,
                                autocomplete: true,
                                allowEmpty: true,
                                listOnEmpty: true,
                            },
                            validator: collectionExistsValidator,
                            formatter: function(cell) {
                                const col = collections.find(c => c.value === cell.getValue());
                                return col ? col.label : '-';
                            }
                        },

                        // Publication Year - Numeric input with custom validator
                        {
                            title: "Year",
                            field: "publication_year",
                            width: 100,
                            headerSort: false,
                            editor: "input",
                            editorParams: {
                                elementAttributes: {
                                    type: "number",
                                    min: "1900",
                                    max: new Date().getFullYear().toString(),
                                },
                            },
                            validator: [yearRangeValidator, {min: 1900, max: new Date().getFullYear()}],
                        },

                        // Pages - Numeric input
                        {
                            title: "Pages",
                            field: "pages",
                            width: 80,
                            headerSort: false,
                            editor: "input",
                            editorParams: {
                                elementAttributes: {
                                    type: "number",
                                    min: "1",
                                },
                            },
                            validator: ["integer", "min:1"],
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },

                        // Access Level - Dropdown with badges
                        {
                            title: "Access",
                            field: "access_level",
                            width: 150,
                            headerSort: false,
                            editor: "list",
                            editorParams: {
                                values: [
                                    {label: "Full Access", value: "full"},
                                    {label: "Limited Access", value: "limited"},
                                    {label: "Unavailable", value: "unavailable"},
                                ],
                            },
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

                        // Physical Type - Dropdown
                        {
                            title: "Type",
                            field: "physical_type",
                            width: 120,
                            headerSort: false,
                            editor: "list",
                            editorParams: {
                                values: [
                                    {label: "Book", value: "book"},
                                    {label: "Journal", value: "journal"},
                                    {label: "Magazine", value: "magazine"},
                                    {label: "Workbook", value: "workbook"},
                                    {label: "Poster", value: "poster"},
                                    {label: "Other", value: "other"},
                                ],
                                allowEmpty: true,
                            },
                            formatter: function(cell) {
                                return cell.getValue() || '-';
                            }
                        },

                        // Featured - Toggle
                        {
                            title: "Featured",
                            field: "is_featured",
                            width: 100,
                            headerSort: false,
                            hozAlign: "center",
                            editor: "tickCross",
                            formatter: "tickCross",
                        },

                        // Active - Toggle
                        {
                            title: "Active",
                            field: "is_active",
                            width: 100,
                            headerSort: false,
                            hozAlign: "center",
                            editor: "tickCross",
                            formatter: "tickCross",
                        },
                    ],
                    });

                    // Track edited cells
                    let editedCells = new Set();

                    table.on("cellEdited", function(cell) {
                        const rowId = cell.getRow().getData().id;
                        const field = cell.getField();
                        const key = rowId + '-' + field;
                        editedCells.add(key);

                        // Update edit count
                        document.getElementById('edit-count').textContent = editedCells.size + ' changes';
                        document.getElementById('save-count').textContent = editedCells.size;

                        // Mark row as changed
                        cell.getRow().getElement().classList.add('row-changed');

                        // Clear validation error on successful edit
                        cell.getElement().removeAttribute('data-validation-error');

                        // Enhanced logging with old and new values
                        console.log('Cell edited:', {
                            row: rowId,
                            field: field,
                            oldValue: cell.getOldValue(),
                            newValue: cell.getValue(),
                        });
                    });

                    // Track any data changes (optional)
                    table.on("dataChanged", function(data) {
                        console.log("Table data changed, current row count:", data.length);
                    });

                    // Handle validation failures
                    table.on("validationFailed", function(cell, value, validators) {
                        // Add error message to cell for hover tooltip
                        const errorMsg = validators.map(v => v.error || v).join(', ');
                        cell.getElement().setAttribute('data-validation-error', errorMsg);
                        console.log('Validation failed:', {
                            field: cell.getField(),
                            value: value,
                            errors: errorMsg
                        });
                    });

                    // Attach validation check to save button
                    document.getElementById('save-changes-btn').addEventListener('click', function() {
                        console.log('Save button clicked, validating changes...');
                        if (validateAllChanges(table)) {
                            console.log('Validation passed! Ready to save.');
                            document.getElementById('status-message').textContent = 'Validation passed - save functionality coming in Phase 8';
                        } else {
                            console.log('Validation failed, cannot save.');
                        }
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

                    // Clipboard events
                    table.on("clipboardCopied", function(clipboard) {
                        console.log('Copied to clipboard:', clipboard);
                        document.getElementById('status-message').textContent = 'Data copied to clipboard';
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 2000);
                    });

                    table.on("clipboardPasted", function(clipboard, rowData, rows) {
                        console.log('Pasted from clipboard:', {
                            clipboard: clipboard,
                            affectedRows: rows.length
                        });
                        document.getElementById('status-message').textContent = `Pasted data into ${rows.length} row(s)`;
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 2000);
                    });

                    table.on("clipboardPasteError", function(clipboard) {
                        console.error('Paste error:', clipboard);
                        document.getElementById('status-message').textContent = 'Error pasting data';
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 3000);
                    });

                    // Get edited data function - returns array of changed book objects
                    window.getEditedData = function() {
                        const editedCells = table.getEditedCells();
                        const changes = {};

                        editedCells.forEach(cell => {
                            const bookId = cell.getRow().getData().id;
                            const field = cell.getField();
                            const value = cell.getValue();

                            if (!changes[bookId]) {
                                changes[bookId] = {id: bookId};
                            }
                            changes[bookId][field] = value;
                        });

                        const result = Object.values(changes);
                        console.log('Edited data retrieved:', result);
                        return result;
                    };

                    // Clear edit history function
                    window.clearEditHistory = function() {
                        const editedCellsArray = table.getEditedCells();
                        editedCellsArray.forEach(cell => {
                            cell.clearEdited();
                        });

                        // Clear visual indicators
                        table.getRows().forEach(row => {
                            row.getElement().classList.remove('row-changed');
                        });

                        // Clear tracking set
                        editedCells.clear();
                        document.getElementById('edit-count').textContent = '0 changes';
                        document.getElementById('save-count').textContent = '0';

                        console.log('Edit history cleared');
                    };

                    // ========================================
                    // PHASE 7: BULK OPERATIONS
                    // ========================================

                    // 7.1 & 7.2: Bulk Update Action
                    document.getElementById('bulk-update-btn').addEventListener('click', function() {
                        const selectedRows = table.getSelectedRows();
                        if (selectedRows.length === 0) {
                            alert('No rows selected. Please select rows using the checkboxes.');
                            return;
                        }

                        document.getElementById('selected-count').textContent = selectedRows.length;
                        document.getElementById('bulk-update-modal').classList.remove('hidden');
                    });

                    // Dynamic field input based on selected field
                    document.getElementById('bulk-field').addEventListener('change', function() {
                        const field = this.value;
                        const valueContainer = document.getElementById('bulk-value-container');

                        // Clear existing input
                        valueContainer.innerHTML = '';

                        if (!field) return;

                        const label = document.createElement('label');
                        label.setAttribute('for', 'bulk-value');
                        label.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2';
                        label.textContent = 'New Value:';
                        valueContainer.appendChild(label);

                        let input;

                        // Create appropriate input based on field type
                        if (field === 'publisher_id') {
                            input = document.createElement('select');
                            input.id = 'bulk-value';
                            input.className = 'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500';

                            const defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = '-- Select Publisher --';
                            input.appendChild(defaultOption);

                            publishers.forEach(pub => {
                                const option = document.createElement('option');
                                option.value = pub.value;
                                option.textContent = pub.label;
                                input.appendChild(option);
                            });
                        } else if (field === 'collection_id') {
                            input = document.createElement('select');
                            input.id = 'bulk-value';
                            input.className = 'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500';

                            const defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = '-- Select Collection --';
                            input.appendChild(defaultOption);

                            collections.forEach(col => {
                                const option = document.createElement('option');
                                option.value = col.value;
                                option.textContent = col.label;
                                input.appendChild(option);
                            });
                        } else if (field === 'access_level') {
                            input = document.createElement('select');
                            input.id = 'bulk-value';
                            input.className = 'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500';

                            const options = [
                                {value: 'full', label: 'Full Access'},
                                {value: 'limited', label: 'Limited Access'},
                                {value: 'unavailable', label: 'Unavailable'}
                            ];

                            options.forEach(opt => {
                                const option = document.createElement('option');
                                option.value = opt.value;
                                option.textContent = opt.label;
                                input.appendChild(option);
                            });
                        } else if (field === 'physical_type') {
                            input = document.createElement('select');
                            input.id = 'bulk-value';
                            input.className = 'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500';

                            const options = [
                                {value: 'book', label: 'Book'},
                                {value: 'journal', label: 'Journal'},
                                {value: 'magazine', label: 'Magazine'},
                                {value: 'workbook', label: 'Workbook'},
                                {value: 'poster', label: 'Poster'},
                                {value: 'other', label: 'Other'}
                            ];

                            options.forEach(opt => {
                                const option = document.createElement('option');
                                option.value = opt.value;
                                option.textContent = opt.label;
                                input.appendChild(option);
                            });
                        } else if (field === 'is_featured' || field === 'is_active') {
                            input = document.createElement('select');
                            input.id = 'bulk-value';
                            input.className = 'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500';

                            const trueOption = document.createElement('option');
                            trueOption.value = 'true';
                            trueOption.textContent = 'Yes';
                            input.appendChild(trueOption);

                            const falseOption = document.createElement('option');
                            falseOption.value = 'false';
                            falseOption.textContent = 'No';
                            input.appendChild(falseOption);
                        }

                        valueContainer.appendChild(input);
                    });

                    // Apply bulk update
                    document.getElementById('apply-bulk-update').addEventListener('click', function() {
                        const field = document.getElementById('bulk-field').value;
                        const valueElement = document.getElementById('bulk-value');

                        if (!field) {
                            alert('Please select a field to update');
                            return;
                        }

                        if (!valueElement) {
                            alert('Please select a field first');
                            return;
                        }

                        let value = valueElement.value;

                        // Convert boolean strings to actual booleans
                        if (value === 'true') value = true;
                        if (value === 'false') value = false;

                        // Convert numeric strings to numbers for ID fields
                        if (field === 'publisher_id' || field === 'collection_id') {
                            value = value ? parseInt(value) : null;
                        }

                        const selectedRows = table.getSelectedRows();
                        let updateCount = 0;

                        selectedRows.forEach(row => {
                            row.update({[field]: value});
                            updateCount++;
                        });

                        document.getElementById('bulk-update-modal').classList.add('hidden');
                        document.getElementById('status-message').textContent = `Updated ${updateCount} rows`;
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 3000);

                        console.log(`Bulk updated ${updateCount} rows: ${field} = ${value}`);
                    });

                    // Cancel bulk update
                    document.getElementById('cancel-bulk-update').addEventListener('click', function() {
                        document.getElementById('bulk-update-modal').classList.add('hidden');
                    });

                    // 7.3: Fill Down Functionality
                    document.getElementById('fill-down-btn').addEventListener('click', function() {
                        const selectedData = table.getSelectedData();

                        if (selectedData.length === 0) {
                            alert('No rows selected. Please select rows using the checkboxes to fill down values.');
                            return;
                        }

                        if (selectedData.length === 1) {
                            alert('Please select at least 2 rows to use fill down.');
                            return;
                        }

                        // Get the first selected row's data
                        const firstRow = selectedData[0];
                        const selectedRows = table.getSelectedRows();

                        // Show field selector modal (simple prompt for now)
                        const field = prompt('Enter the field name to fill down (e.g., publisher_id, access_level, is_featured):\n\nAvailable fields:\n- publisher_id\n- collection_id\n- access_level\n- physical_type\n- is_featured\n- is_active');

                        if (!field || !firstRow.hasOwnProperty(field)) {
                            if (field) alert('Invalid field name: ' + field);
                            return;
                        }

                        const value = firstRow[field];
                        let fillCount = 0;

                        // Apply value to all selected rows (skip first one)
                        selectedRows.slice(1).forEach(row => {
                            row.update({[field]: value});
                            fillCount++;
                        });

                        document.getElementById('status-message').textContent = `Filled down ${field} to ${fillCount} rows`;
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 3000);

                        console.log(`Fill down ${field} = ${value} to ${fillCount} rows`);
                    });

                    // 7.4: Find & Replace Functionality
                    document.getElementById('find-replace-btn').addEventListener('click', function() {
                        document.getElementById('find-replace-modal').classList.remove('hidden');
                        document.getElementById('find-results').textContent = '';
                    });

                    // Find matches
                    document.getElementById('find-btn').addEventListener('click', function() {
                        const field = document.getElementById('find-field').value;
                        let findText = document.getElementById('find-text').value;
                        const matchCase = document.getElementById('match-case').checked;

                        if (!findText) {
                            alert('Please enter text to find');
                            return;
                        }

                        const data = table.getData();
                        const matches = [];

                        data.forEach((row, index) => {
                            let cellValue = String(row[field] || '');
                            let searchText = findText;

                            if (!matchCase) {
                                cellValue = cellValue.toLowerCase();
                                searchText = searchText.toLowerCase();
                            }

                            if (cellValue.includes(searchText)) {
                                matches.push({
                                    row: index + 1,
                                    id: row.id,
                                    value: row[field]
                                });
                            }
                        });

                        const resultsDiv = document.getElementById('find-results');
                        if (matches.length > 0) {
                            resultsDiv.textContent = `Found ${matches.length} match(es)`;
                            resultsDiv.className = 'text-sm text-green-600 dark:text-green-400 min-h-[20px]';
                        } else {
                            resultsDiv.textContent = 'No matches found';
                            resultsDiv.className = 'text-sm text-red-600 dark:text-red-400 min-h-[20px]';
                        }

                        console.log('Find results:', matches);
                    });

                    // Replace all matches
                    document.getElementById('replace-all-btn').addEventListener('click', function() {
                        const field = document.getElementById('find-field').value;
                        let findText = document.getElementById('find-text').value;
                        const replaceText = document.getElementById('replace-text').value;
                        const matchCase = document.getElementById('match-case').checked;

                        if (!findText) {
                            alert('Please enter text to find');
                            return;
                        }

                        let count = 0;
                        table.getRows().forEach(row => {
                            const rowData = row.getData();
                            let cellValue = String(rowData[field] || '');
                            let updated = cellValue;

                            if (matchCase) {
                                // Case-sensitive replace
                                updated = cellValue.replaceAll(findText, replaceText);
                            } else {
                                // Case-insensitive replace
                                const regex = new RegExp(findText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
                                updated = cellValue.replace(regex, replaceText);
                            }

                            if (updated !== cellValue) {
                                row.update({[field]: updated});
                                count++;
                            }
                        });

                        document.getElementById('find-replace-modal').classList.add('hidden');
                        document.getElementById('status-message').textContent = `Replaced ${count} instance(s)`;
                        setTimeout(() => {
                            document.getElementById('status-message').textContent = '';
                        }, 3000);

                        console.log(`Replaced ${count} instances of "${findText}" with "${replaceText}"`);
                    });

                    // Cancel find & replace
                    document.getElementById('cancel-find-replace').addEventListener('click', function() {
                        document.getElementById('find-replace-modal').classList.add('hidden');
                    });

                    console.log('Tabulator table initialized with remote data, editors, edit tracking, and bulk operations');
                } // end initializeTable
            });
        </script>
    @endpush
</x-filament-panels::page>
