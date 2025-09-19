@props(['content', 'settings'])

@php
    $images = $content['images'] ?? [];
    $layout = $settings['layout'] ?? 'grid';
    $columns = $settings['columns'] ?? 3;
    $spacing = $settings['spacing'] ?? 'medium';
    $showCaptions = $settings['show_captions'] ?? true;
    $lightbox = $settings['lightbox'] ?? true;
    $aspectRatio = $settings['aspect_ratio'] ?? 'auto';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['gallery-block', 'mb-6'];

    // Spacing classes
    switch($spacing) {
        case 'small':
            $gapClass = 'gap-2';
            break;
        case 'large':
            $gapClass = 'gap-8';
            break;
        default:
            $gapClass = 'gap-4';
    }

    // Layout classes
    if ($layout === 'grid') {
        $containerClasses[] = 'grid';
        $containerClasses[] = $gapClass;

        // Responsive grid columns
        switch($columns) {
            case 1:
                $containerClasses[] = 'grid-cols-1';
                break;
            case 2:
                $containerClasses[] = 'grid-cols-1 md:grid-cols-2';
                break;
            case 4:
                $containerClasses[] = 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4';
                break;
            case 5:
                $containerClasses[] = 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5';
                break;
            case 6:
                $containerClasses[] = 'grid-cols-2 md:grid-cols-4 lg:grid-cols-6';
                break;
            default:
                $containerClasses[] = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
        }
    } elseif ($layout === 'masonry') {
        $containerClasses[] = 'columns-1 md:columns-2 lg:columns-3';
        $containerClasses[] = $gapClass;
    } elseif ($layout === 'carousel') {
        $containerClasses[] = 'flex overflow-x-auto';
        $containerClasses[] = $gapClass;
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Image container classes
    $imageContainerClasses = ['gallery-item', 'group', 'relative'];

    if ($layout === 'masonry') {
        $imageContainerClasses[] = 'break-inside-avoid mb-4';
    } elseif ($layout === 'carousel') {
        $imageContainerClasses[] = 'flex-shrink-0 w-64';
    }

    // Aspect ratio classes
    $aspectRatioClass = '';
    switch($aspectRatio) {
        case 'square':
            $aspectRatioClass = 'aspect-square';
            break;
        case '16:9':
            $aspectRatioClass = 'aspect-video';
            break;
        case '4:3':
            $aspectRatioClass = 'aspect-[4/3]';
            break;
        case '3:2':
            $aspectRatioClass = 'aspect-[3/2]';
            break;
        case '2:1':
            $aspectRatioClass = 'aspect-[2/1]';
            break;
    }

    $galleryId = $elementId ?: 'gallery-' . uniqid();
@endphp

@if(!empty($images))
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif
         @if($lightbox) data-lightbox-gallery="{{ $galleryId }}" @endif>

        @foreach($images as $index => $image)
            @php
                $url = $image['url'] ?? '';
                $alt = $image['alt'] ?? "Gallery image " . ($index + 1);
                $caption = $image['caption'] ?? '';
                $thumbnail = $image['thumbnail'] ?? $url;
            @endphp

            @if($url)
                <div class="{{ implode(' ', $imageContainerClasses) }}">
                    @if($lightbox)
                        <a href="{{ $url }}"
                           data-lightbox="{{ $galleryId }}"
                           @if($caption) data-title="{{ $caption }}" @endif
                           class="block overflow-hidden rounded-lg">
                    @endif

                    <div class="@if($aspectRatio !== 'auto') {{ $aspectRatioClass }} @endif overflow-hidden rounded-lg bg-gray-100">
                        <img src="{{ $thumbnail }}"
                             alt="{{ $alt }}"
                             class="w-full h-full @if($aspectRatio !== 'auto') object-cover @else object-contain @endif transition-transform duration-300 group-hover:scale-105"
                             loading="lazy"
                             decoding="async">

                        @if($lightbox)
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-2xl"></i>
                            </div>
                        @endif
                    </div>

                    @if($lightbox)
                        </a>
                    @endif

                    @if($showCaptions && $caption)
                        <div class="mt-2 text-sm text-gray-600 text-center">
                            {{ $caption }}
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    @if($lightbox)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Simple lightbox implementation
                const galleryLinks = document.querySelectorAll('[data-lightbox="{{ $galleryId }}"]');
                const lightboxId = 'lightbox-{{ $galleryId }}';

                if (galleryLinks.length > 0) {
                    // Create lightbox HTML
                    const lightboxHTML = `
                        <div id="${lightboxId}" class="fixed inset-0 z-50 hidden bg-black bg-opacity-90 flex items-center justify-center p-4">
                            <div class="relative max-w-5xl max-h-full">
                                <button class="absolute top-4 right-4 text-white text-2xl z-10 hover:text-gray-300" onclick="closeLightbox('${lightboxId}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                <img class="max-w-full max-h-full object-contain" id="${lightboxId}-img" src="" alt="">
                                <div class="absolute bottom-4 left-4 right-4 text-white text-center" id="${lightboxId}-caption"></div>
                                <button class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-gray-300" onclick="previousImage('${lightboxId}')">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-gray-300" onclick="nextImage('${lightboxId}')">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    `;

                    // Add lightbox to body if not exists
                    if (!document.getElementById(lightboxId)) {
                        document.body.insertAdjacentHTML('beforeend', lightboxHTML);
                    }

                    // Store gallery images
                    const galleryImages = Array.from(galleryLinks).map(link => ({
                        src: link.href,
                        title: link.dataset.title || '',
                        alt: link.querySelector('img')?.alt || ''
                    }));

                    let currentImageIndex = 0;

                    // Add click handlers
                    galleryLinks.forEach((link, index) => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            currentImageIndex = index;
                            showLightboxImage(lightboxId, galleryImages[currentImageIndex]);
                            document.getElementById(lightboxId).classList.remove('hidden');
                            document.body.style.overflow = 'hidden';
                        });
                    });

                    // Global functions for navigation
                    window.closeLightbox = function(id) {
                        document.getElementById(id).classList.add('hidden');
                        document.body.style.overflow = '';
                    };

                    window.previousImage = function(id) {
                        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
                        showLightboxImage(id, galleryImages[currentImageIndex]);
                    };

                    window.nextImage = function(id) {
                        currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
                        showLightboxImage(id, galleryImages[currentImageIndex]);
                    };

                    function showLightboxImage(id, image) {
                        const img = document.getElementById(id + '-img');
                        const caption = document.getElementById(id + '-caption');
                        img.src = image.src;
                        img.alt = image.alt;
                        caption.textContent = image.title;
                    }

                    // Close on escape key
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            window.closeLightbox(lightboxId);
                        } else if (e.key === 'ArrowLeft') {
                            window.previousImage(lightboxId);
                        } else if (e.key === 'ArrowRight') {
                            window.nextImage(lightboxId);
                        }
                    });

                    // Close on backdrop click
                    document.getElementById(lightboxId).addEventListener('click', function(e) {
                        if (e.target === this) {
                            window.closeLightbox(lightboxId);
                        }
                    });
                }
            });
        </script>
        @endpush
    @endif

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-images text-4xl mb-2"></i>
        <p>No images in gallery</p>
    </div>
@endif