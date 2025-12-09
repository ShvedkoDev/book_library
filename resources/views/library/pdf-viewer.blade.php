@extends('layouts.library')

@section('title', $book->title . ' - PDF Viewer')

@push('styles')
<style>
    .pdf-viewer-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: #525659;
        z-index: 9999;
        display: flex;
        flex-direction: column;
    }

    .pdf-toolbar {
        background: #323639;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .pdf-toolbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pdf-toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pdf-title {
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
        color: #ffffff;
    }

    .pdf-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pdf-btn {
        background: #474b4f;
        border: none;
        color: #ffffff !important;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pdf-btn:hover:not(:disabled) {
        background: #5a5e62;
        color: #ffffff !important;
        text-decoration: none;
    }

    .pdf-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pdf-btn i {
        color: #ffffff;
    }

    .pdf-page-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .pdf-page-input {
        width: 50px;
        text-align: center;
        background: #474b4f;
        border: 1px solid #5a5e62;
        color: white;
        padding: 0.25rem;
        border-radius: 4px;
    }

    .pdf-canvas-container {
        flex: 1;
        overflow: auto;
        display: flex;
        justify-content: center;
        padding: 2rem;
        position: relative;
    }

    .pdf-canvas-wrapper {
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    #pdf-canvas {
        display: block;
        background: white;
        max-width: 100%;
        height: auto;
    }

    .pdf-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 4rem;
        font-weight: bold;
        color: rgba(255, 0, 0, 0.1);
        pointer-events: none;
        white-space: nowrap;
        user-select: none;
    }

    .pdf-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 1.2rem;
    }

    .access-warning {
        background: #fff3cd;
        color: #856404;
        padding: 1rem;
        text-align: center;
        border-bottom: 1px solid #ffc107;
    }
</style>
@endpush

@section('content')
<div class="pdf-viewer-container">
    @if($book->access_level === 'limited')
    <div class="access-warning">
        <i class="fal fa-lock"></i> Limited Access - Download disabled. Viewing only.
    </div>
    @endif

    <div class="pdf-toolbar">
        <div class="pdf-toolbar-left">
            <a href="{{ route('library.show', $book->slug) }}" class="pdf-btn">
                <i class="fal fa-arrow-left"></i> Back
            </a>
            <h1 class="pdf-title">{{ $book->title }}</h1>
        </div>
        <div class="pdf-toolbar-right">
            <div class="pdf-controls">
                <button id="prev-page" class="pdf-btn">
                    <i class="fal fa-chevron-left"></i>
                </button>
                <div class="pdf-page-info">
                    <input type="number" id="page-num" class="pdf-page-input" value="1" min="1">
                    <span>/ <span id="page-count">-</span></span>
                </div>
                <button id="next-page" class="pdf-btn">
                    <i class="fal fa-chevron-right"></i>
                </button>
            </div>
            <button id="zoom-out" class="pdf-btn">
                <i class="fal fa-minus"></i>
            </button>
            <button id="zoom-in" class="pdf-btn">
                <i class="fal fa-plus"></i>
            </button>
            @if($book->access_level === 'full')
            <a href="{{ route('library.download', ['book' => $book->id, 'file' => $file->id]) }}" class="pdf-btn">
                <i class="fal fa-download"></i> Download
            </a>
            @endif
        </div>
    </div>

    <div class="pdf-canvas-container">
        <div class="pdf-loading" id="loading">Loading PDF...</div>
        <div class="pdf-canvas-wrapper">
            <canvas id="pdf-canvas"></canvas>
            @if($book->access_level === 'limited')
            <div class="pdf-watermark">LIMITED ACCESS</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// PDF.js configuration
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// PDF viewer state
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let scale = 1.5;
const canvas = document.getElementById('pdf-canvas');
const ctx = canvas.getContext('2d');

// Load PDF from route
const pdfUrl = '{{ route('library.view-pdf-direct', ['book' => $book->id, 'file' => $file->id]) }}';

// Disable right-click on canvas (prevent context menu)
canvas.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    return false;
});

// Disable text selection on canvas
canvas.style.userSelect = 'none';
canvas.style.webkitUserSelect = 'none';

/**
 * Render the specified page
 */
function renderPage(num) {
    pageRendering = true;

    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        const renderTask = page.render(renderContext);

        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });

    document.getElementById('page-num').value = num;
}

/**
 * Queue page render if another render in progress
 */
function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

/**
 * Previous page
 */
function onPrevPage() {
    if (pageNum <= 1) {
        return;
    }
    pageNum--;
    queueRenderPage(pageNum);
    updateButtons();
}

/**
 * Next page
 */
function onNextPage() {
    if (pageNum >= pdfDoc.numPages) {
        return;
    }
    pageNum++;
    queueRenderPage(pageNum);
    updateButtons();
}

/**
 * Update button states
 */
function updateButtons() {
    document.getElementById('prev-page').disabled = pageNum <= 1;
    document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;
}

/**
 * Zoom in
 */
function onZoomIn() {
    if (scale < 3) {
        scale += 0.25;
        queueRenderPage(pageNum);
    }
}

/**
 * Zoom out
 */
function onZoomOut() {
    if (scale > 0.5) {
        scale -= 0.25;
        queueRenderPage(pageNum);
    }
}

/**
 * Go to specific page
 */
function onPageInputChange() {
    const input = document.getElementById('page-num');
    let num = parseInt(input.value);

    if (num < 1) num = 1;
    if (num > pdfDoc.numPages) num = pdfDoc.numPages;

    pageNum = num;
    queueRenderPage(pageNum);
    updateButtons();
}

// Event listeners
document.getElementById('prev-page').addEventListener('click', onPrevPage);
document.getElementById('next-page').addEventListener('click', onNextPage);
document.getElementById('zoom-in').addEventListener('click', onZoomIn);
document.getElementById('zoom-out').addEventListener('click', onZoomOut);
document.getElementById('page-num').addEventListener('change', onPageInputChange);

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') onPrevPage();
    if (e.key === 'ArrowRight') onNextPage();
    if (e.key === '+' || e.key === '=') onZoomIn();
    if (e.key === '-') onZoomOut();
});

// Load the PDF
pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
    pdfDoc = pdf;
    document.getElementById('page-count').textContent = pdf.numPages;
    document.getElementById('loading').style.display = 'none';

    // Render first page
    renderPage(pageNum);
    updateButtons();
}).catch(function(error) {
    document.getElementById('loading').textContent = 'Error loading PDF: ' + error.message;
    console.error('Error loading PDF:', error);
});
</script>
@endpush
