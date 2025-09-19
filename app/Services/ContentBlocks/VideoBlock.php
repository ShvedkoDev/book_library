<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class VideoBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Video Block';
    }

    public function getDescription(): string
    {
        return 'Embed videos or upload video files with poster images';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-video-camera';
    }

    public function getCategory(): string
    {
        return 'Media';
    }

    protected function getType(): string
    {
        return 'video';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Video Content')
                ->schema([
                    Forms\Components\Select::make('video_type')
                        ->label('Video Type')
                        ->options([
                            'embed' => 'Embed (YouTube, Vimeo, etc.)',
                            'upload' => 'Upload Video File',
                        ])
                        ->default('embed')
                        ->live(),

                    Forms\Components\Textarea::make('embed_code')
                        ->label('Embed Code')
                        ->required()
                        ->rows(4)
                        ->helperText('Paste the embed code from YouTube, Vimeo, or other video platforms')
                        ->visible(fn ($get) => $get('video_type') === 'embed'),

                    Forms\Components\FileUpload::make('video_file')
                        ->label('Video File')
                        ->required()
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->maxSize(102400) // 100MB
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\FileUpload::make('poster_image')
                        ->label('Poster Image')
                        ->image()
                        ->maxSize(5120)
                        ->helperText('Preview image shown before video plays')
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\TextInput::make('title')
                        ->label('Video Title')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Video Description')
                        ->maxLength(500)
                        ->rows(3),
                ]),

            Forms\Components\Section::make('Video Settings')
                ->schema([
                    Forms\Components\Select::make('aspect_ratio')
                        ->label('Aspect Ratio')
                        ->options([
                            '16:9' => '16:9 (Widescreen)',
                            '4:3' => '4:3 (Standard)',
                            '1:1' => '1:1 (Square)',
                            '21:9' => '21:9 (Ultrawide)',
                        ])
                        ->default('16:9'),

                    Forms\Components\Toggle::make('autoplay')
                        ->label('Autoplay')
                        ->default(false)
                        ->helperText('Note: Most browsers block autoplay with sound')
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\Toggle::make('controls')
                        ->label('Show Controls')
                        ->default(true)
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\Toggle::make('loop')
                        ->label('Loop Video')
                        ->default(false)
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\Toggle::make('muted')
                        ->label('Muted by Default')
                        ->default(false)
                        ->visible(fn ($get) => $get('video_type') === 'upload'),

                    Forms\Components\Toggle::make('lazy_loading')
                        ->label('Lazy Loading')
                        ->default(true),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'video_type' => 'required|in:embed,upload',
            'embed_code' => 'required_if:video_type,embed|string',
            'video_file' => 'required_if:video_type,upload|file|mimes:mp4,webm,ogg|max:102400',
            'poster_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'aspect_ratio' => 'in:16:9,4:3,1:1,21:9',
            'autoplay' => 'boolean',
            'controls' => 'boolean',
            'loop' => 'boolean',
            'muted' => 'boolean',
            'lazy_loading' => 'boolean',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $videoClasses = ['video-block'];
        $videoClasses[] = 'aspect-' . str_replace(':', '-', $content['aspect_ratio'] ?? '16-9');

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['title'])) {
            $html .= '<h3 class="video-title">' . e($content['title']) . '</h3>';
        }

        $html .= '<div class="' . implode(' ', $videoClasses) . '">';

        if ($content['video_type'] === 'embed') {
            // For embed videos, we need to sanitize the embed code
            $embedCode = $content['embed_code'];
            // Basic sanitization - in production, use a more robust solution
            $embedCode = strip_tags($embedCode, '<iframe><embed><object><param>');
            $html .= $embedCode;
        } else {
            // For uploaded videos
            $videoAttributes = [
                'class="uploaded-video"',
                'src="' . e($content['video_file']) . '"',
            ];

            if (!empty($content['poster_image'])) {
                $videoAttributes[] = 'poster="' . e($content['poster_image']) . '"';
            }

            if (!empty($content['controls'])) {
                $videoAttributes[] = 'controls';
            }

            if (!empty($content['autoplay'])) {
                $videoAttributes[] = 'autoplay';
            }

            if (!empty($content['loop'])) {
                $videoAttributes[] = 'loop';
            }

            if (!empty($content['muted'])) {
                $videoAttributes[] = 'muted';
            }

            if (!empty($content['lazy_loading'])) {
                $videoAttributes[] = 'loading="lazy"';
            }

            $html .= '<video ' . implode(' ', $videoAttributes) . '>';
            $html .= '<p>Your browser does not support the video tag.</p>';
            $html .= '</video>';
        }

        $html .= '</div>';

        if (!empty($content['description'])) {
            $html .= '<div class="video-description">' . nl2br(e($content['description'])) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
