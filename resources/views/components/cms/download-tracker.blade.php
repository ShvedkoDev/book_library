@props([
    'media',
    'text' => 'Download',
    'class' => '',
    'icon' => true,
    'trackAnalytics' => true,
])

@php
    if (!$media) {
        return;
    }

    $downloadUrl = route('cms.media.download', ['media' => $media->id]);
    $fileName = $media->file_name;
    $fileSize = app('cms.media')->formatBytes($media->size ?? 0);
    $downloadCount = $media->getCustomProperty('download_count', 0);
@endphp

<div {{ $attributes->merge(['class' => 'media-download-wrapper ' . $class]) }}>
    <a
        href="{{ $downloadUrl }}"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 download-link"
        data-media-id="{{ $media->id }}"
        data-file-name="{{ $fileName }}"
        @if($trackAnalytics)
            onclick="trackDownload(this)"
        @endif
        download="{{ $fileName }}"
        title="Download {{ $fileName }} ({{ $fileSize }})"
    >
        @if($icon)
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m5-5v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2m2-1v1m0 0V4a2 2 0 012-2h2a2 2 0 012 2v1m0 0v1"></path>
            </svg>
        @endif

        <span class="download-text">{{ $text }}</span>

        <span class="ml-2 text-xs opacity-75">({{ $fileSize }})</span>
    </a>

    @if($downloadCount > 0)
        <div class="mt-1 text-xs text-gray-500">
            Downloaded {{ number_format($downloadCount) }} times
        </div>
    @endif
</div>

@if($trackAnalytics)
    @push('scripts')
    <script>
    function trackDownload(element) {
        const mediaId = element.dataset.mediaId;
        const fileName = element.dataset.fileName;

        // Send analytics data
        if (typeof gtag !== 'undefined') {
            gtag('event', 'file_download', {
                'file_name': fileName,
                'media_id': mediaId,
                'file_extension': fileName.split('.').pop()
            });
        }

        // Send to custom analytics endpoint
        fetch('{{ route("cms.analytics.track") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                event: 'media_download',
                media_id: mediaId,
                file_name: fileName,
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent,
                referrer: document.referrer
            })
        }).catch(error => {
            console.warn('Analytics tracking failed:', error);
        });

        // Visual feedback
        const originalText = element.querySelector('.download-text').textContent;
        element.querySelector('.download-text').textContent = 'Downloading...';

        setTimeout(() => {
            element.querySelector('.download-text').textContent = originalText;
        }, 2000);
    }

    // Track download completion (for supported browsers)
    document.addEventListener('DOMContentLoaded', function() {
        const downloadLinks = document.querySelectorAll('.download-link');

        downloadLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const mediaId = this.dataset.mediaId;

                // Update download count in the background
                fetch('{{ route("cms.media.increment-download") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        media_id: mediaId
                    })
                }).catch(error => {
                    console.warn('Download count update failed:', error);
                });
            });
        });
    });
    </script>
    @endpush
@endif

@push('styles')
<style>
.media-download-wrapper {
    position: relative;
}

.download-link {
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.download-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.download-link:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Loading state */
.download-link.downloading {
    opacity: 0.75;
    cursor: wait;
}

.download-link.downloading::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 8px;
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translateY(-50%);
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* File type icons */
.download-link[data-file-name$=".pdf"] {
    background-color: #dc2626;
}

.download-link[data-file-name$=".doc"],
.download-link[data-file-name$=".docx"] {
    background-color: #2563eb;
}

.download-link[data-file-name$=".xls"],
.download-link[data-file-name$=".xlsx"] {
    background-color: #059669;
}

.download-link[data-file-name$=".ppt"],
.download-link[data-file-name$=".pptx"] {
    background-color: #d97706;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .download-link {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .download-link svg {
        width: 1rem;
        height: 1rem;
    }
}
</style>
@endpush