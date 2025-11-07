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

                    // Table columns with editors
                    columns: [
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

                    console.log('Tabulator table initialized with remote data, editors, and edit tracking');
                } // end initializeTable
            });
        </script>
    @endpush
</x-filament-panels::page>
