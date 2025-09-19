@props(['content', 'settings'])

@php
    $items = $content['items'] ?? [];
    $style = $settings['style'] ?? 'default';
    $allowMultiple = $settings['allow_multiple'] ?? false;
    $defaultOpen = $settings['default_open'] ?? null;
    $animated = $settings['animated'] ?? true;
    $bordered = $settings['bordered'] ?? true;
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['accordion-block', 'space-y-2', 'mb-6'];

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build item classes based on style
    switch($style) {
        case 'minimal':
            $itemClass = 'border-b border-gray-200';
            $headerClass = 'w-full text-left py-4 flex justify-between items-center hover:bg-gray-50 focus:outline-none focus:bg-gray-50';
            $contentClass = 'pb-4 text-gray-600';
            $iconClass = 'fas fa-chevron-down text-gray-400 transform transition-transform duration-200';
            break;
        case 'card':
            $itemClass = 'bg-white border border-gray-200 rounded-lg shadow-sm';
            $headerClass = 'w-full text-left p-4 flex justify-between items-center hover:bg-gray-50 focus:outline-none focus:bg-gray-50 rounded-lg';
            $contentClass = 'px-4 pb-4 text-gray-600';
            $iconClass = 'fas fa-chevron-down text-gray-400 transform transition-transform duration-200';
            break;
        case 'modern':
            $itemClass = 'bg-white border border-gray-200 rounded-lg overflow-hidden';
            $headerClass = 'w-full text-left p-6 flex justify-between items-center hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors';
            $contentClass = 'px-6 pb-6 text-gray-600';
            $iconClass = 'w-5 h-5 text-gray-400 transform transition-transform duration-200';
            break;
        case 'flush':
            $itemClass = 'border-b border-gray-200 last:border-b-0';
            $headerClass = 'w-full text-left py-3 flex justify-between items-center hover:text-blue-600 focus:outline-none focus:text-blue-600';
            $contentClass = 'pb-3 text-gray-600';
            $iconClass = 'fas fa-plus text-gray-400 transform transition-transform duration-200';
            break;
        default:
            $itemClass = $bordered ? 'border border-gray-200 rounded-lg' : '';
            $headerClass = 'w-full text-left p-4 flex justify-between items-center hover:bg-gray-50 focus:outline-none focus:bg-gray-50';
            $contentClass = 'px-4 pb-4 text-gray-600';
            $iconClass = 'fas fa-chevron-down text-gray-400 transform transition-transform duration-200';
    }

    // Generate unique ID for accordion functionality
    $accordionId = $elementId ?: 'accordion-' . uniqid();
@endphp

@if(!empty($items))
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif
         data-accordion="{{ $accordionId }}"
         data-allow-multiple="{{ $allowMultiple ? 'true' : 'false' }}">

        @foreach($items as $index => $item)
            @php
                $title = $item['title'] ?? "Item " . ($index + 1);
                $content = $item['content'] ?? '';
                $isOpen = $defaultOpen === $index || (is_array($defaultOpen) && in_array($index, $defaultOpen));
                $itemId = "{$accordionId}-item-{$index}";
            @endphp

            <div class="{{ $itemClass }} accordion-item"
                 data-accordion-item="{{ $index }}">

                <button type="button"
                        class="{{ $headerClass }} accordion-header"
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                        aria-controls="{{ $itemId }}-content"
                        data-accordion-trigger="{{ $index }}">

                    <span class="font-medium text-gray-900">{{ $title }}</span>

                    @if($style === 'modern')
                        <svg class="{{ $iconClass }} {{ $isOpen ? 'rotate-180' : '' }}"
                             fill="none" viewBox="0 0 20 20" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    @elseif($style === 'flush')
                        <i class="{{ $iconClass }} {{ $isOpen ? 'fa-minus rotate-180' : 'fa-plus' }}"></i>
                    @else
                        <i class="{{ $iconClass }} {{ $isOpen ? 'rotate-180' : '' }}"></i>
                    @endif
                </button>

                <div id="{{ $itemId }}-content"
                     class="accordion-content {{ $isOpen ? '' : 'hidden' }} {{ $animated ? 'transition-all duration-300 ease-in-out' : '' }}"
                     aria-labelledby="{{ $itemId }}-header">

                    <div class="{{ $contentClass }}">
                        @if(strip_tags($content) !== $content)
                            <div class="prose prose-sm max-w-none">
                                {!! $content !!}
                            </div>
                        @else
                            {!! nl2br(e($content)) !!}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accordion = document.querySelector('[data-accordion="{{ $accordionId }}"]');
            if (!accordion) return;

            const allowMultiple = accordion.dataset.allowMultiple === 'true';
            const triggers = accordion.querySelectorAll('[data-accordion-trigger]');

            triggers.forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const itemIndex = this.dataset.accordionTrigger;
                    const item = accordion.querySelector(`[data-accordion-item="${itemIndex}"]`);
                    const content = item.querySelector('.accordion-content');
                    const icon = this.querySelector('i, svg');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    // Close other items if multiple not allowed
                    if (!allowMultiple && !isExpanded) {
                        triggers.forEach(otherTrigger => {
                            if (otherTrigger !== this) {
                                const otherItem = accordion.querySelector(`[data-accordion-item="${otherTrigger.dataset.accordionTrigger}"]`);
                                const otherContent = otherItem.querySelector('.accordion-content');
                                const otherIcon = otherTrigger.querySelector('i, svg');

                                otherTrigger.setAttribute('aria-expanded', 'false');
                                otherContent.classList.add('hidden');

                                // Reset icon
                                if (otherIcon) {
                                    if (otherIcon.tagName === 'svg') {
                                        otherIcon.classList.remove('rotate-180');
                                    } else if (otherIcon.classList.contains('fa-minus')) {
                                        otherIcon.classList.remove('fa-minus', 'rotate-180');
                                        otherIcon.classList.add('fa-plus');
                                    } else {
                                        otherIcon.classList.remove('rotate-180');
                                    }
                                }
                            }
                        });
                    }

                    // Toggle current item
                    if (isExpanded) {
                        // Close
                        this.setAttribute('aria-expanded', 'false');
                        content.classList.add('hidden');

                        if (icon) {
                            if (icon.tagName === 'svg') {
                                icon.classList.remove('rotate-180');
                            } else if (icon.classList.contains('fa-minus')) {
                                icon.classList.remove('fa-minus', 'rotate-180');
                                icon.classList.add('fa-plus');
                            } else {
                                icon.classList.remove('rotate-180');
                            }
                        }
                    } else {
                        // Open
                        this.setAttribute('aria-expanded', 'true');
                        content.classList.remove('hidden');

                        if (icon) {
                            if (icon.tagName === 'svg') {
                                icon.classList.add('rotate-180');
                            } else if (icon.classList.contains('fa-plus')) {
                                icon.classList.remove('fa-plus');
                                icon.classList.add('fa-minus', 'rotate-180');
                            } else {
                                icon.classList.add('rotate-180');
                            }
                        }
                    }

                    // Optional: Smooth scroll to opened item
                    if (!isExpanded) {
                        setTimeout(() => {
                            const rect = item.getBoundingClientRect();
                            const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;
                            if (!isVisible) {
                                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        }, 150);
                    }
                });
            });

            // Keyboard navigation
            accordion.addEventListener('keydown', function(e) {
                if (e.target.matches('[data-accordion-trigger]')) {
                    const currentIndex = parseInt(e.target.dataset.accordionTrigger);
                    let targetIndex;

                    switch(e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            targetIndex = currentIndex + 1 < triggers.length ? currentIndex + 1 : 0;
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            targetIndex = currentIndex > 0 ? currentIndex - 1 : triggers.length - 1;
                            break;
                        case 'Home':
                            e.preventDefault();
                            targetIndex = 0;
                            break;
                        case 'End':
                            e.preventDefault();
                            targetIndex = triggers.length - 1;
                            break;
                    }

                    if (targetIndex !== undefined) {
                        triggers[targetIndex].focus();
                    }
                }
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .accordion-content.hidden {
            display: none;
        }

        @if($animated)
        .accordion-content {
            overflow: hidden;
        }

        .accordion-item .accordion-content {
            transition: max-height 0.3s ease-in-out;
        }
        @endif

        /* Focus styles for accessibility */
        .accordion-header:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Hover effects */
        .accordion-header:hover {
            background-color: #f9fafb;
        }

        /* Icon transition */
        .accordion-header i,
        .accordion-header svg {
            transition: transform 0.2s ease-in-out;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .accordion-header {
                padding: 12px 16px;
                font-size: 14px;
            }

            .accordion-content {
                padding: 0 16px 16px;
                font-size: 14px;
            }
        }
    </style>
    @endpush

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-list text-4xl mb-2"></i>
        <p>No accordion items provided</p>
    </div>
@endif