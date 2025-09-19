@props(['content', 'settings'])

@php
    $headers = $content['headers'] ?? [];
    $rows = $content['rows'] ?? [];
    $caption = $content['caption'] ?? '';
    $style = $settings['style'] ?? 'default';
    $striped = $settings['striped'] ?? false;
    $bordered = $settings['bordered'] ?? true;
    $hoverable = $settings['hoverable'] ?? true;
    $responsive = $settings['responsive'] ?? true;
    $sortable = $settings['sortable'] ?? false;
    $searchable = $settings['searchable'] ?? false;
    $pagination = $settings['pagination'] ?? false;
    $pageSize = $settings['page_size'] ?? 10;
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['table-block', 'mb-6'];

    if ($responsive) {
        $containerClasses[] = 'overflow-x-auto';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build table classes
    $tableClasses = ['min-w-full'];

    // Style classes
    switch($style) {
        case 'minimal':
            $tableClasses[] = 'divide-y divide-gray-200';
            $headerClass = 'bg-gray-50';
            $cellClass = 'px-6 py-4 whitespace-nowrap text-sm';
            break;
        case 'modern':
            $tableClasses[] = 'divide-y divide-gray-200 shadow overflow-hidden rounded-lg';
            $headerClass = 'bg-gray-800 text-white';
            $cellClass = 'px-6 py-4 whitespace-nowrap text-sm';
            break;
        case 'compact':
            $tableClasses[] = 'divide-y divide-gray-200';
            $headerClass = 'bg-gray-100';
            $cellClass = 'px-3 py-2 text-sm';
            break;
        default:
            $tableClasses[] = 'divide-y divide-gray-200';
            $headerClass = 'bg-gray-50';
            $cellClass = 'px-6 py-4 whitespace-nowrap text-sm';
    }

    if ($bordered) {
        $tableClasses[] = 'border border-gray-200';
    }

    if ($striped) {
        $tableClasses[] = 'table-striped';
    }

    if ($hoverable) {
        $tableClasses[] = 'table-hoverable';
    }

    // Generate unique ID for interactive features
    $tableId = $elementId ?: 'table-' . uniqid();

    // Prepare data for JSON if using interactive features
    $tableData = [
        'headers' => $headers,
        'rows' => $rows,
    ];
@endphp

@if(!empty($headers) || !empty($rows))
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif>

        @if($caption)
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">{{ $caption }}</h3>
            </div>
        @endif

        @if($searchable)
            <div class="mb-4">
                <div class="relative">
                    <input type="text"
                           id="{{ $tableId }}-search"
                           placeholder="Search table..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-wrapper" id="{{ $tableId }}-wrapper">
            <table class="{{ implode(' ', $tableClasses) }}" id="{{ $tableId }}">
                @if(!empty($headers))
                    <thead class="{{ $headerClass }}">
                        <tr>
                            @foreach($headers as $index => $header)
                                <th scope="col"
                                    class="{{ $cellClass }} text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $sortable ? 'cursor-pointer hover:bg-gray-100' : '' }}"
                                    @if($sortable) data-sort="{{ $index }}" @endif>
                                    <div class="flex items-center">
                                        {{ $header }}
                                        @if($sortable)
                                            <span class="ml-2 sort-icon">
                                                <i class="fas fa-sort text-gray-400"></i>
                                            </span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif

                @if(!empty($rows))
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rows as $rowIndex => $row)
                            <tr class="table-row {{ $striped && $rowIndex % 2 === 1 ? 'bg-gray-50' : 'bg-white' }} {{ $hoverable ? 'hover:bg-gray-50' : '' }}">
                                @foreach($row as $cellIndex => $cell)
                                    <td class="{{ $cellClass }} text-gray-900">
                                        @if(is_array($cell))
                                            @if(isset($cell['type']) && $cell['type'] === 'link')
                                                <a href="{{ $cell['url'] ?? '#' }}"
                                                   class="text-blue-600 hover:text-blue-800 underline"
                                                   @if(isset($cell['target'])) target="{{ $cell['target'] }}" @endif>
                                                    {{ $cell['text'] ?? $cell['url'] }}
                                                </a>
                                            @elseif(isset($cell['type']) && $cell['type'] === 'image')
                                                <img src="{{ $cell['src'] ?? '' }}"
                                                     alt="{{ $cell['alt'] ?? '' }}"
                                                     class="h-8 w-8 rounded-full object-cover">
                                            @elseif(isset($cell['type']) && $cell['type'] === 'badge')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cell['class'] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cell['text'] ?? '' }}
                                                </span>
                                            @else
                                                {{ $cell['text'] ?? $cell }}
                                            @endif
                                        @else
                                            @if(strip_tags($cell) !== $cell)
                                                {!! $cell !!}
                                            @else
                                                {{ $cell }}
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>

        @if($pagination && count($rows) > $pageSize)
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="{{ $tableId }}-start">1</span> to <span id="{{ $tableId }}-end">{{ min($pageSize, count($rows)) }}</span> of <span id="{{ $tableId }}-total">{{ count($rows) }}</span> results
                </div>
                <div class="flex space-x-2">
                    <button id="{{ $tableId }}-prev"
                            class="px-3 py-1 border border-gray-300 rounded text-sm bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        Previous
                    </button>
                    <div id="{{ $tableId }}-pages" class="flex space-x-1"></div>
                    <button id="{{ $tableId }}-next"
                            class="px-3 py-1 border border-gray-300 rounded text-sm bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if($sortable || $searchable || $pagination)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tableId = '{{ $tableId }}';
                const table = document.getElementById(tableId);
                const tableData = @json($tableData);
                let currentData = [...tableData.rows];
                let currentPage = 1;
                const pageSize = {{ $pageSize }};

                @if($searchable)
                // Search functionality
                const searchInput = document.getElementById(tableId + '-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        currentData = tableData.rows.filter(row => {
                            return row.some(cell => {
                                const cellText = typeof cell === 'object' && cell.text ? cell.text : cell;
                                return cellText.toString().toLowerCase().includes(searchTerm);
                            });
                        });
                        currentPage = 1;
                        updateTable();
                        @if($pagination) updatePagination(); @endif
                    });
                }
                @endif

                @if($sortable)
                // Sort functionality
                const sortHeaders = table.querySelectorAll('[data-sort]');
                let sortColumn = null;
                let sortDirection = 'asc';

                sortHeaders.forEach(header => {
                    header.addEventListener('click', function() {
                        const column = parseInt(this.dataset.sort);

                        if (sortColumn === column) {
                            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            sortColumn = column;
                            sortDirection = 'asc';
                        }

                        // Update sort icons
                        sortHeaders.forEach(h => {
                            const icon = h.querySelector('.sort-icon i');
                            icon.className = 'fas fa-sort text-gray-400';
                        });

                        const activeIcon = this.querySelector('.sort-icon i');
                        activeIcon.className = sortDirection === 'asc'
                            ? 'fas fa-sort-up text-gray-600'
                            : 'fas fa-sort-down text-gray-600';

                        // Sort data
                        currentData.sort((a, b) => {
                            const aVal = typeof a[column] === 'object' && a[column].text ? a[column].text : a[column];
                            const bVal = typeof b[column] === 'object' && b[column].text ? b[column].text : b[column];

                            if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
                            if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
                            return 0;
                        });

                        currentPage = 1;
                        updateTable();
                        @if($pagination) updatePagination(); @endif
                    });
                });
                @endif

                @if($pagination)
                // Pagination functionality
                function updatePagination() {
                    const totalPages = Math.ceil(currentData.length / pageSize);
                    const startRecord = (currentPage - 1) * pageSize + 1;
                    const endRecord = Math.min(currentPage * pageSize, currentData.length);

                    document.getElementById(tableId + '-start').textContent = startRecord;
                    document.getElementById(tableId + '-end').textContent = endRecord;
                    document.getElementById(tableId + '-total').textContent = currentData.length;

                    const prevBtn = document.getElementById(tableId + '-prev');
                    const nextBtn = document.getElementById(tableId + '-next');
                    const pagesContainer = document.getElementById(tableId + '-pages');

                    prevBtn.disabled = currentPage === 1;
                    nextBtn.disabled = currentPage === totalPages || totalPages === 0;

                    // Generate page buttons
                    pagesContainer.innerHTML = '';
                    for (let i = 1; i <= totalPages; i++) {
                        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                            const btn = document.createElement('button');
                            btn.textContent = i;
                            btn.className = `px-3 py-1 border border-gray-300 rounded text-sm ${i === currentPage ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'}`;
                            btn.addEventListener('click', () => {
                                currentPage = i;
                                updateTable();
                                updatePagination();
                            });
                            pagesContainer.appendChild(btn);
                        } else if (i === currentPage - 2 || i === currentPage + 2) {
                            const ellipsis = document.createElement('span');
                            ellipsis.textContent = '...';
                            ellipsis.className = 'px-2 py-1 text-gray-500';
                            pagesContainer.appendChild(ellipsis);
                        }
                    }
                }

                document.getElementById(tableId + '-prev').addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        updateTable();
                        updatePagination();
                    }
                });

                document.getElementById(tableId + '-next').addEventListener('click', () => {
                    const totalPages = Math.ceil(currentData.length / pageSize);
                    if (currentPage < totalPages) {
                        currentPage++;
                        updateTable();
                        updatePagination();
                    }
                });

                updatePagination();
                @endif

                function updateTable() {
                    const tbody = table.querySelector('tbody');
                    @if($pagination)
                    const startIndex = (currentPage - 1) * pageSize;
                    const endIndex = startIndex + pageSize;
                    const pageData = currentData.slice(startIndex, endIndex);
                    @else
                    const pageData = currentData;
                    @endif

                    tbody.innerHTML = '';
                    pageData.forEach((row, rowIndex) => {
                        const tr = document.createElement('tr');
                        tr.className = `table-row ${
                            @if($striped) rowIndex % 2 === 1 ? 'bg-gray-50' : 'bg-white' @else 'bg-white' @endif
                        } ${ @if($hoverable) 'hover:bg-gray-50' @endif }`;

                        row.forEach(cell => {
                            const td = document.createElement('td');
                            td.className = '{{ $cellClass }} text-gray-900';

                            if (typeof cell === 'object' && cell.type) {
                                if (cell.type === 'link') {
                                    td.innerHTML = `<a href="${cell.url || '#'}" class="text-blue-600 hover:text-blue-800 underline"${cell.target ? ` target="${cell.target}"` : ''}>${cell.text || cell.url}</a>`;
                                } else if (cell.type === 'image') {
                                    td.innerHTML = `<img src="${cell.src || ''}" alt="${cell.alt || ''}" class="h-8 w-8 rounded-full object-cover">`;
                                } else if (cell.type === 'badge') {
                                    td.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${cell.class || 'bg-gray-100 text-gray-800'}">${cell.text || ''}</span>`;
                                } else {
                                    td.textContent = cell.text || cell;
                                }
                            } else {
                                td.textContent = cell;
                            }

                            tr.appendChild(td);
                        });

                        tbody.appendChild(tr);
                    });
                }
            });
        </script>
        @endpush
    @endif

    @push('styles')
    <style>
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }

        .table-hoverable tbody tr:hover {
            background-color: #f3f4f6 !important;
        }

        @media (max-width: 768px) {
            .table-block .{{ $cellClass }} {
                padding: 8px 12px;
                font-size: 14px;
            }

            .table-block th {
                font-size: 12px;
            }
        }
    </style>
    @endpush

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-table text-4xl mb-2"></i>
        <p>No table data provided</p>
    </div>
@endif