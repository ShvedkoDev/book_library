<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Thumbnail Generation
    |--------------------------------------------------------------------------
    |
    | Enable automatic thumbnail generation from PDF first page.
    | Requires Imagick PHP extension to be installed.
    |
    */
    'enable_pdf_extraction' => env('THUMBNAIL_ENABLE_PDF_EXTRACTION', false),

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Dimensions
    |--------------------------------------------------------------------------
    |
    | Default dimensions for generated thumbnails.
    |
    */
    'width' => env('THUMBNAIL_WIDTH', 400),
    'height' => env('THUMBNAIL_HEIGHT', 600),

    /*
    |--------------------------------------------------------------------------
    | JPEG Quality
    |--------------------------------------------------------------------------
    |
    | Quality for JPEG thumbnails generated from PDFs (1-100).
    |
    */
    'jpeg_quality' => env('THUMBNAIL_JPEG_QUALITY', 85),

    /*
    |--------------------------------------------------------------------------
    | Cache Generated Thumbnails
    |--------------------------------------------------------------------------
    |
    | Whether to save generated placeholder thumbnails to disk for caching.
    |
    */
    'cache_placeholders' => env('THUMBNAIL_CACHE_PLACEHOLDERS', false),
];
