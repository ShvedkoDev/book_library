<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the CMS system.
    | You may modify these settings according to your application's needs.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Page Templates
    |--------------------------------------------------------------------------
    |
    | Define the available page templates for content creation.
    | Each template should have a corresponding Blade view file.
    |
    */
    'templates' => [
        'default' => [
            'name' => 'Default Template',
            'description' => 'Standard page layout with content blocks',
            'view' => 'cms.templates.default',
            'preview' => '/images/templates/default-preview.jpg',
            'sections' => ['header', 'content', 'sidebar', 'footer'],
        ],
        'full-width' => [
            'name' => 'Full Width Template',
            'description' => 'Full width layout without sidebar',
            'view' => 'cms.templates.full-width',
            'preview' => '/images/templates/full-width-preview.jpg',
            'sections' => ['header', 'content', 'footer'],
        ],
        'landing' => [
            'name' => 'Landing Page',
            'description' => 'Marketing landing page template',
            'view' => 'cms.templates.landing',
            'preview' => '/images/templates/landing-preview.jpg',
            'sections' => ['hero', 'features', 'cta', 'footer'],
        ],
        'article' => [
            'name' => 'Article Template',
            'description' => 'Blog post and article layout',
            'view' => 'cms.templates.article',
            'preview' => '/images/templates/article-preview.jpg',
            'sections' => ['header', 'content', 'author', 'related', 'footer'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Block Types
    |--------------------------------------------------------------------------
    |
    | Configuration for all available content block types.
    | This defines the structure and behavior of each block type.
    |
    */
    'blocks' => [
        'text' => [
            'name' => 'Text Block',
            'description' => 'Rich text content with formatting options',
            'icon' => 'heroicon-o-document-text',
            'category' => 'content',
            'fields' => [
                'content' => [
                    'type' => 'rich_editor',
                    'label' => 'Content',
                    'required' => true,
                ],
            ],
            'settings' => [
                'alignment' => ['left', 'center', 'right', 'justify'],
                'text_size' => ['small', 'medium', 'large'],
                'background_color' => 'color_picker',
                'text_color' => 'color_picker',
                'padding' => 'spacing',
                'margin' => 'spacing',
            ],
        ],
        'image' => [
            'name' => 'Image Block',
            'description' => 'Single image with caption and alt text',
            'icon' => 'heroicon-o-photo',
            'category' => 'media',
            'fields' => [
                'image' => [
                    'type' => 'file_upload',
                    'label' => 'Image',
                    'required' => true,
                    'accept' => 'image/*',
                ],
                'caption' => [
                    'type' => 'text',
                    'label' => 'Caption',
                ],
                'alt_text' => [
                    'type' => 'text',
                    'label' => 'Alt Text',
                ],
                'link_url' => [
                    'type' => 'url',
                    'label' => 'Link URL',
                ],
            ],
            'settings' => [
                'alignment' => ['left', 'center', 'right'],
                'size' => ['small', 'medium', 'large', 'full'],
                'border_radius' => 'slider',
                'shadow' => 'toggle',
            ],
        ],
        'gallery' => [
            'name' => 'Gallery Block',
            'description' => 'Multiple images in a gallery layout',
            'icon' => 'heroicon-o-rectangle-stack',
            'category' => 'media',
            'fields' => [
                'images' => [
                    'type' => 'file_upload',
                    'label' => 'Images',
                    'required' => true,
                    'multiple' => true,
                    'accept' => 'image/*',
                ],
                'layout' => [
                    'type' => 'select',
                    'label' => 'Layout',
                    'options' => ['grid', 'masonry', 'carousel'],
                    'default' => 'grid',
                ],
            ],
            'settings' => [
                'columns' => ['1', '2', '3', '4', '5', '6'],
                'spacing' => 'slider',
                'lightbox' => 'toggle',
                'captions' => 'toggle',
            ],
        ],
        'video' => [
            'name' => 'Video Block',
            'description' => 'Embedded or uploaded video content',
            'icon' => 'heroicon-o-play',
            'category' => 'media',
            'fields' => [
                'source_type' => [
                    'type' => 'select',
                    'label' => 'Source Type',
                    'options' => ['youtube', 'vimeo', 'upload', 'embed'],
                    'default' => 'youtube',
                ],
                'video_url' => [
                    'type' => 'url',
                    'label' => 'Video URL',
                ],
                'video_file' => [
                    'type' => 'file_upload',
                    'label' => 'Video File',
                    'accept' => 'video/*',
                ],
                'embed_code' => [
                    'type' => 'textarea',
                    'label' => 'Embed Code',
                ],
                'poster_image' => [
                    'type' => 'file_upload',
                    'label' => 'Poster Image',
                    'accept' => 'image/*',
                ],
            ],
            'settings' => [
                'autoplay' => 'toggle',
                'controls' => 'toggle',
                'loop' => 'toggle',
                'muted' => 'toggle',
                'aspect_ratio' => ['16:9', '4:3', '21:9', '1:1'],
            ],
        ],
        'quote' => [
            'name' => 'Quote Block',
            'description' => 'Blockquote with author attribution',
            'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
            'category' => 'content',
            'fields' => [
                'quote' => [
                    'type' => 'textarea',
                    'label' => 'Quote',
                    'required' => true,
                ],
                'author' => [
                    'type' => 'text',
                    'label' => 'Author',
                ],
                'author_title' => [
                    'type' => 'text',
                    'label' => 'Author Title',
                ],
                'author_image' => [
                    'type' => 'file_upload',
                    'label' => 'Author Image',
                    'accept' => 'image/*',
                ],
            ],
            'settings' => [
                'style' => ['default', 'modern', 'minimal', 'bordered'],
                'alignment' => ['left', 'center', 'right'],
                'show_quotes' => 'toggle',
                'background_color' => 'color_picker',
            ],
        ],
        'code' => [
            'name' => 'Code Block',
            'description' => 'Syntax highlighted code snippets',
            'icon' => 'heroicon-o-code-bracket',
            'category' => 'content',
            'fields' => [
                'code' => [
                    'type' => 'textarea',
                    'label' => 'Code',
                    'required' => true,
                ],
                'language' => [
                    'type' => 'select',
                    'label' => 'Language',
                    'options' => ['html', 'css', 'javascript', 'php', 'python', 'java', 'json', 'xml'],
                    'default' => 'html',
                ],
                'filename' => [
                    'type' => 'text',
                    'label' => 'Filename',
                ],
            ],
            'settings' => [
                'theme' => ['default', 'dark', 'github', 'monokai'],
                'line_numbers' => 'toggle',
                'copy_button' => 'toggle',
                'wrap_lines' => 'toggle',
            ],
        ],
        'cta' => [
            'name' => 'Call to Action',
            'description' => 'Call-to-action button with customizable styling',
            'icon' => 'heroicon-o-megaphone',
            'category' => 'marketing',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Title',
                    'required' => true,
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Description',
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => 'Button Text',
                    'required' => true,
                ],
                'button_url' => [
                    'type' => 'url',
                    'label' => 'Button URL',
                    'required' => true,
                ],
                'secondary_button_text' => [
                    'type' => 'text',
                    'label' => 'Secondary Button Text',
                ],
                'secondary_button_url' => [
                    'type' => 'url',
                    'label' => 'Secondary Button URL',
                ],
            ],
            'settings' => [
                'style' => ['default', 'modern', 'minimal', 'gradient'],
                'alignment' => ['left', 'center', 'right'],
                'button_style' => ['primary', 'secondary', 'outline', 'ghost'],
                'background_color' => 'color_picker',
                'text_color' => 'color_picker',
            ],
        ],
        'divider' => [
            'name' => 'Divider',
            'description' => 'Visual separator with customizable styling',
            'icon' => 'heroicon-o-minus',
            'category' => 'layout',
            'fields' => [],
            'settings' => [
                'style' => ['line', 'dashed', 'dotted', 'double', 'gradient'],
                'width' => ['25%', '50%', '75%', '100%'],
                'alignment' => ['left', 'center', 'right'],
                'color' => 'color_picker',
                'thickness' => 'slider',
                'spacing' => 'spacing',
            ],
        ],
        'table' => [
            'name' => 'Table',
            'description' => 'Responsive data table',
            'icon' => 'heroicon-o-table-cells',
            'category' => 'content',
            'fields' => [
                'headers' => [
                    'type' => 'repeater',
                    'label' => 'Headers',
                    'schema' => [
                        'text' => [
                            'type' => 'text',
                            'label' => 'Header Text',
                        ],
                    ],
                ],
                'rows' => [
                    'type' => 'repeater',
                    'label' => 'Rows',
                    'schema' => [
                        'cells' => [
                            'type' => 'repeater',
                            'label' => 'Cells',
                            'schema' => [
                                'text' => [
                                    'type' => 'text',
                                    'label' => 'Cell Text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'style' => ['default', 'striped', 'bordered', 'borderless'],
                'responsive' => 'toggle',
                'sortable' => 'toggle',
                'searchable' => 'toggle',
            ],
        ],
        'accordion' => [
            'name' => 'Accordion',
            'description' => 'Collapsible content sections',
            'icon' => 'heroicon-o-bars-3-bottom-left',
            'category' => 'layout',
            'fields' => [
                'items' => [
                    'type' => 'repeater',
                    'label' => 'Accordion Items',
                    'schema' => [
                        'title' => [
                            'type' => 'text',
                            'label' => 'Title',
                            'required' => true,
                        ],
                        'content' => [
                            'type' => 'rich_editor',
                            'label' => 'Content',
                            'required' => true,
                        ],
                        'icon' => [
                            'type' => 'icon_picker',
                            'label' => 'Icon',
                        ],
                    ],
                ],
            ],
            'settings' => [
                'allow_multiple_open' => 'toggle',
                'first_item_open' => 'toggle',
                'style' => ['default', 'minimal', 'bordered', 'card'],
                'icon_position' => ['left', 'right'],
            ],
        ],
        'embed' => [
            'name' => 'Embed',
            'description' => 'Embedded content from external sources',
            'icon' => 'heroicon-o-globe-alt',
            'category' => 'media',
            'fields' => [
                'embed_code' => [
                    'type' => 'textarea',
                    'label' => 'Embed Code',
                    'required' => true,
                ],
                'source_name' => [
                    'type' => 'text',
                    'label' => 'Source Name',
                ],
                'caption' => [
                    'type' => 'text',
                    'label' => 'Caption',
                ],
            ],
            'settings' => [
                'responsive' => 'toggle',
                'aspect_ratio' => ['16:9', '4:3', '21:9', '1:1', 'auto'],
                'lazy_load' => 'toggle',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Default SEO settings and templates for meta tag generation.
    |
    */
    'seo' => [
        'title_template' => '{{ title }} | {{ site_name }}',
        'title_separator' => ' | ',
        'description_length' => 160,
        'keywords_max' => 10,
        'robots' => 'index,follow',
        'canonical_base_url' => env('APP_URL'),

        'meta_tags' => [
            'og:type' => 'website',
            'og:locale' => 'en_US',
            'twitter:card' => 'summary_large_image',
        ],

        'structured_data' => [
            'organization' => [
                'name' => env('APP_NAME'),
                'url' => env('APP_URL'),
                'logo' => env('APP_URL') . '/images/logo.png',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for media uploads, image processing, and file management.
    |
    */
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'path' => 'cms/media',
        'max_file_size' => 50 * 1024 * 1024, // 50MB in bytes

        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/webm',
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
        ],

        'allowed_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'videos' => ['mp4', 'webm', 'ogg', 'avi', 'mov'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac'],
        ],

        // Media Collections Configuration
        'collections' => [
            'page_featured' => [
                'name' => 'Page Featured Images',
                'description' => 'Main featured images for pages',
                'single_file' => true,
                'accepts_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'disk' => 'public',
                'path' => 'cms/pages/featured',
            ],
            'page_gallery' => [
                'name' => 'Page Gallery',
                'description' => 'Gallery images for pages',
                'single_file' => false,
                'accepts_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'disk' => 'public',
                'path' => 'cms/pages/gallery',
            ],
            'content_blocks' => [
                'name' => 'Content Block Media',
                'description' => 'Media files used within content blocks',
                'single_file' => false,
                'accepts_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4', 'video/webm'],
                'disk' => 'public',
                'path' => 'cms/content-blocks',
            ],
            'documents' => [
                'name' => 'Documents',
                'description' => 'PDF files, documents, and spreadsheets',
                'single_file' => false,
                'accepts_mime_types' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ],
                'disk' => 'public',
                'path' => 'cms/documents',
            ],
            'videos' => [
                'name' => 'Video Files',
                'description' => 'Video files with poster frame generation',
                'single_file' => false,
                'accepts_mime_types' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/webm'],
                'disk' => 'public',
                'path' => 'cms/videos',
            ],
            'seo_images' => [
                'name' => 'SEO Images',
                'description' => 'OpenGraph and Twitter Card images',
                'single_file' => false,
                'accepts_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
                'disk' => 'public',
                'path' => 'cms/seo',
            ],
        ],

        // Image Processing & Optimization
        'optimization' => [
            'jpeg_quality' => 85,
            'png_compression' => 6,
            'webp_quality' => 80,
            'avif_quality' => 75,
        ],

        'generate_webp' => true,
        'generate_retina' => true,
        'auto_alt_text' => false, // Enable when AI service is configured

        // Responsive Image Conversions
        'conversions' => [
            'thumbnail' => [
                'width' => 150,
                'height' => 150,
                'fit' => 'crop',
                'quality' => 80,
            ],
            'small' => [
                'width' => 400,
                'height' => 300,
                'fit' => 'contain',
                'quality' => 85,
            ],
            'medium' => [
                'width' => 800,
                'height' => 600,
                'fit' => 'contain',
                'quality' => 85,
            ],
            'large' => [
                'width' => 1200,
                'height' => 900,
                'fit' => 'contain',
                'quality' => 90,
            ],
            'extra_large' => [
                'width' => 1920,
                'height' => 1080,
                'fit' => 'contain',
                'quality' => 90,
            ],
            'og_image' => [
                'width' => 1200,
                'height' => 630,
                'fit' => 'crop',
                'quality' => 90,
            ],
            'twitter_image' => [
                'width' => 1024,
                'height' => 512,
                'fit' => 'crop',
                'quality' => 90,
            ],
            'hero' => [
                'width' => 1920,
                'height' => 600,
                'fit' => 'crop',
                'quality' => 90,
            ],
        ],

        // Media Query Breakpoints for Responsive Images
        'media_queries' => [
            'thumbnail' => null,
            'small' => '(max-width: 480px)',
            'medium' => '(max-width: 768px)',
            'large' => '(max-width: 1200px)',
            'extra_large' => '(min-width: 1201px)',
        ],

        // Security Settings
        'virus_scan' => env('CMS_MEDIA_VIRUS_SCAN', false),
        'sanitize_svg' => true,
        'watermark' => [
            'enabled' => false,
            'image' => 'watermark.png',
            'opacity' => 0.3,
            'position' => 'bottom-right',
        ],

        // CDN Configuration
        'cdn' => [
            'enabled' => env('CMS_MEDIA_CDN_ENABLED', false),
            'url' => env('CMS_MEDIA_CDN_URL'),
            'pull_zone' => env('CMS_MEDIA_CDN_PULL_ZONE'),
        ],

        // Storage Analytics
        'analytics' => [
            'track_downloads' => true,
            'track_views' => true,
            'generate_reports' => true,
        ],

        // Cleanup Settings
        'cleanup' => [
            'auto_delete_unused' => false,
            'unused_retention_days' => 30,
            'temp_file_retention_hours' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Caching settings for improved performance.
    |
    */
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'ttl' => env('CMS_CACHE_TTL', 3600), // 1 hour
        'tags' => [
            'cms_pages',
            'cms_categories',
            'cms_navigation',
            'cms_settings',
        ],
        'keys' => [
            'page' => 'cms.page.{slug}',
            'category' => 'cms.category.{slug}',
            'navigation' => 'cms.navigation',
            'settings' => 'cms.settings.{group}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | URL and Routing Configuration
    |--------------------------------------------------------------------------
    |
    | URL patterns and routing configuration for CMS pages.
    |
    */
    'routing' => [
        'prefix' => env('CMS_ROUTE_PREFIX', 'cms'),
        'middleware' => ['web'],
        'admin_prefix' => 'admin/cms',

        'patterns' => [
            'page' => '/page/{slug}',
            'category' => '/category/{slug}',
            'search' => '/search',
            'sitemap' => '/sitemap.xml',
            'feed' => '/feed',
        ],

        'constraints' => [
            'slug' => '[a-zA-Z0-9\-_]+',
            'id' => '[0-9]+',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Configuration
    |--------------------------------------------------------------------------
    |
    | Content-related settings and defaults.
    |
    */
    'content' => [
        'excerpt_length' => 160,
        'per_page' => 10,
        'rich_editor' => 'tiptap', // tiptap, tinymce, ckeditor
        'allow_comments' => false,
        'auto_save_interval' => 30, // seconds

        'statuses' => [
            'draft' => 'Draft',
            'published' => 'Published',
            'scheduled' => 'Scheduled',
            'archived' => 'Archived',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for the CMS.
    |
    */
    'security' => [
        'sanitize_html' => true,
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 's', 'a', 'ul', 'ol', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code',
            'pre', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        ],
        'csrf_protection' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Analytics and tracking configuration.
    |
    */
    'analytics' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
        'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
        'track_page_views' => true,
        'track_downloads' => true,
        'track_outbound_links' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Backup settings for content and media.
    |
    */
    'backup' => [
        'enabled' => env('CMS_BACKUP_ENABLED', false),
        'disk' => env('CMS_BACKUP_DISK', 'local'),
        'schedule' => 'daily',
        'retention_days' => 30,
        'include_media' => true,
    ],

];