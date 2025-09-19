<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class ImageBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Image Block';
    }

    public function getDescription(): string
    {
        return 'Single image with caption, alt text, and alignment options';
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
        return 'image';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Image Content')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Image')
                        ->image()
                        ->required()
                        ->maxSize(10240) // 10MB
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1', null])
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('alt_text')
                        ->label('Alt Text')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Describe the image for accessibility'),

                    Forms\Components\Textarea::make('caption')
                        ->label('Caption')
                        ->maxLength(500)
                        ->rows(2),

                    Forms\Components\TextInput::make('link_url')
                        ->label('Link URL')
                        ->url()
                        ->helperText('Optional: Make the image clickable'),

                    Forms\Components\Toggle::make('open_in_new_tab')
                        ->label('Open link in new tab')
                        ->default(false)
                        ->visible(fn ($get) => !empty($get('link_url'))),
                ]),

            Forms\Components\Section::make('Image Settings')
                ->schema([
                    Forms\Components\Select::make('image_alignment')
                        ->label('Image Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                            'full-width' => 'Full Width',
                        ])
                        ->default('center'),

                    Forms\Components\Select::make('image_size')
                        ->label('Image Size')
                        ->options([
                            'small' => 'Small (300px)',
                            'medium' => 'Medium (600px)',
                            'large' => 'Large (900px)',
                            'full' => 'Full Size',
                        ])
                        ->default('medium'),

                    Forms\Components\Toggle::make('rounded_corners')
                        ->label('Rounded Corners')
                        ->default(false),

                    Forms\Components\Toggle::make('shadow')
                        ->label('Drop Shadow')
                        ->default(false),

                    Forms\Components\Toggle::make('lazy_loading')
                        ->label('Lazy Loading')
                        ->default(true)
                        ->helperText('Improves page load performance'),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'alt_text' => 'required|string|max:255',
            'caption' => 'nullable|string|max:500',
            'link_url' => 'nullable|url',
            'open_in_new_tab' => 'boolean',
            'image_alignment' => 'in:left,center,right,full-width',
            'image_size' => 'in:small,medium,large,full',
            'rounded_corners' => 'boolean',
            'shadow' => 'boolean',
            'lazy_loading' => 'boolean',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $imageClasses = ['block-image'];
        
        // Add alignment classes
        if (!empty($content['image_alignment'])) {
            $imageClasses[] = 'align-' . $content['image_alignment'];
        }

        // Add size classes
        if (!empty($content['image_size'])) {
            $imageClasses[] = 'size-' . $content['image_size'];
        }

        // Add styling classes
        if (!empty($content['rounded_corners'])) {
            $imageClasses[] = 'rounded';
        }

        if (!empty($content['shadow'])) {
            $imageClasses[] = 'shadow';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        $imageHtml = '<img src="' . e($content['image']) . '" alt="' . e($content['alt_text']) . '" class="' . implode(' ', $imageClasses) . '"';
        
        if (!empty($content['lazy_loading'])) {
            $imageHtml .= ' loading="lazy"';
        }
        
        $imageHtml .= '>';

        if (!empty($content['link_url'])) {
            $target = !empty($content['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '';
            $html .= '<a href="' . e($content['link_url']) . '"' . $target . '>' . $imageHtml . '</a>';
        } else {
            $html .= $imageHtml;
        }

        if (!empty($content['caption'])) {
            $html .= '<figcaption class="image-caption">' . e($content['caption']) . '</figcaption>';
        }

        $html .= '</div>';

        return $html;
    }
}
