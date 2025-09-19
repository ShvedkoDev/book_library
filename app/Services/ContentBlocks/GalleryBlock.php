<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class GalleryBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Gallery Block';
    }

    public function getDescription(): string
    {
        return 'Multiple images with lightbox, captions, and grid layout';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public function getCategory(): string
    {
        return 'Media';
    }

    protected function getType(): string
    {
        return 'gallery';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Gallery Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Gallery Title')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Gallery Description')
                        ->maxLength(500)
                        ->rows(3),

                    Forms\Components\Repeater::make('images')
                        ->label('Gallery Images')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('Image')
                                ->image()
                                ->required()
                                ->maxSize(10240)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                ->imageEditor(),

                            Forms\Components\TextInput::make('alt_text')
                                ->label('Alt Text')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Textarea::make('caption')
                                ->label('Caption')
                                ->maxLength(300)
                                ->rows(2),

                            Forms\Components\TextInput::make('link_url')
                                ->label('Link URL')
                                ->url(),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Add Image')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Gallery Layout')
                ->schema([
                    Forms\Components\Select::make('layout_type')
                        ->label('Layout Type')
                        ->options([
                            'grid' => 'Grid Layout',
                            'masonry' => 'Masonry Layout',
                            'carousel' => 'Carousel/Slider',
                        ])
                        ->default('grid')
                        ->live(),

                    Forms\Components\Select::make('columns')
                        ->label('Columns')
                        ->options([
                            '1' => '1 Column',
                            '2' => '2 Columns',
                            '3' => '3 Columns',
                            '4' => '4 Columns',
                            '5' => '5 Columns',
                            '6' => '6 Columns',
                        ])
                        ->default('3')
                        ->visible(fn ($get) => in_array($get('layout_type'), ['grid', 'masonry'])),

                    Forms\Components\Select::make('image_size')
                        ->label('Image Size')
                        ->options([
                            'small' => 'Small (200px)',
                            'medium' => 'Medium (300px)',
                            'large' => 'Large (400px)',
                        ])
                        ->default('medium'),

                    Forms\Components\Select::make('gap_size')
                        ->label('Gap Size')
                        ->options([
                            'none' => 'No Gap',
                            'small' => 'Small Gap',
                            'medium' => 'Medium Gap',
                            'large' => 'Large Gap',
                        ])
                        ->default('medium'),

                    Forms\Components\Toggle::make('enable_lightbox')
                        ->label('Enable Lightbox')
                        ->default(true)
                        ->helperText('Allow images to be viewed in full-screen lightbox'),

                    Forms\Components\Toggle::make('show_captions')
                        ->label('Show Captions')
                        ->default(true),

                    Forms\Components\Toggle::make('lazy_loading')
                        ->label('Lazy Loading')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Carousel Settings')
                ->schema([
                    Forms\Components\Toggle::make('autoplay')
                        ->label('Autoplay')
                        ->default(false),

                    Forms\Components\TextInput::make('autoplay_speed')
                        ->label('Autoplay Speed (seconds)')
                        ->numeric()
                        ->default(5)
                        ->minValue(1)
                        ->maxValue(30)
                        ->visible(fn ($get) => $get('autoplay')),

                    Forms\Components\Toggle::make('show_navigation')
                        ->label('Show Navigation Arrows')
                        ->default(true),

                    Forms\Components\Toggle::make('show_indicators')
                        ->label('Show Indicators/Dots')
                        ->default(true),
                ])
                ->visible(fn ($get) => $get('layout_type') === 'carousel'),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'images' => 'required|array|min:1',
            'images.*.image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'images.*.alt_text' => 'required|string|max:255',
            'images.*.caption' => 'nullable|string|max:300',
            'images.*.link_url' => 'nullable|url',
            'layout_type' => 'in:grid,masonry,carousel',
            'columns' => 'in:1,2,3,4,5,6',
            'image_size' => 'in:small,medium,large',
            'gap_size' => 'in:none,small,medium,large',
            'enable_lightbox' => 'boolean',
            'show_captions' => 'boolean',
            'lazy_loading' => 'boolean',
            'autoplay' => 'boolean',
            'autoplay_speed' => 'nullable|numeric|min:1|max:30',
            'show_navigation' => 'boolean',
            'show_indicators' => 'boolean',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $galleryClasses = ['gallery-block'];
        $galleryClasses[] = 'layout-' . ($content['layout_type'] ?? 'grid');
        $galleryClasses[] = 'columns-' . ($content['columns'] ?? '3');
        $galleryClasses[] = 'size-' . ($content['image_size'] ?? 'medium');
        $galleryClasses[] = 'gap-' . ($content['gap_size'] ?? 'medium');

        if (!empty($content['enable_lightbox'])) {
            $galleryClasses[] = 'lightbox-enabled';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['title'])) {
            $html .= '<h3 class="gallery-title">' . e($content['title']) . '</h3>';
        }

        if (!empty($content['description'])) {
            $html .= '<div class="gallery-description">' . nl2br(e($content['description'])) . '</div>';
        }

        $html .= '<div class="' . implode(' ', $galleryClasses) . '"';
        
        if ($content['layout_type'] === 'carousel') {
            $carouselSettings = [];
            if (!empty($content['autoplay'])) {
                $carouselSettings[] = 'data-autoplay="true"';
                $carouselSettings[] = 'data-autoplay-speed="' . ($content['autoplay_speed'] ?? 5) . '"';
            }
            if (!empty($content['show_navigation'])) {
                $carouselSettings[] = 'data-navigation="true"';
            }
            if (!empty($content['show_indicators'])) {
                $carouselSettings[] = 'data-indicators="true"';
            }
            $html .= ' ' . implode(' ', $carouselSettings);
        }
        
        $html .= '>';

        if (!empty($content['images']) && is_array($content['images'])) {
            foreach ($content['images'] as $index => $image) {
                $html .= '<div class="gallery-item">';
                
                $imageHtml = '<img src="' . e($image['image']) . '" alt="' . e($image['alt_text']) . '" class="gallery-image"';
                
                if (!empty($content['lazy_loading'])) {
                    $imageHtml .= ' loading="lazy"';
                }
                
                $imageHtml .= '>';

                if (!empty($content['enable_lightbox'])) {
                    $html .= '<a href="' . e($image['image']) . '" class="lightbox-trigger" data-gallery="gallery-' . uniqid() . '">';
                    $html .= $imageHtml;
                    $html .= '</a>';
                } elseif (!empty($image['link_url'])) {
                    $html .= '<a href="' . e($image['link_url']) . '">';
                    $html .= $imageHtml;
                    $html .= '</a>';
                } else {
                    $html .= $imageHtml;
                }

                if (!empty($content['show_captions']) && !empty($image['caption'])) {
                    $html .= '<div class="gallery-caption">' . e($image['caption']) . '</div>';
                }

                $html .= '</div>';
            }
        }

        $html .= '</div></div>';

        return $html;
    }
}
