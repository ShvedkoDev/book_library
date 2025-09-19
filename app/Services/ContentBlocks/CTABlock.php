<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class CTABlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Call-to-Action Block';
    }

    public function getDescription(): string
    {
        return 'Call-to-action with button, colors, and links';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public function getCategory(): string
    {
        return 'Interactive';
    }

    protected function getType(): string
    {
        return 'cta';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('CTA Content')
                ->schema([
                    Forms\Components\TextInput::make('headline')
                        ->label('Headline')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(3),

                    Forms\Components\TextInput::make('button_text')
                        ->label('Button Text')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('button_url')
                        ->label('Button URL')
                        ->required()
                        ->url(),

                    Forms\Components\Toggle::make('open_in_new_tab')
                        ->label('Open in New Tab')
                        ->default(false),

                    Forms\Components\TextInput::make('secondary_button_text')
                        ->label('Secondary Button Text')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('secondary_button_url')
                        ->label('Secondary Button URL')
                        ->url()
                        ->visible(fn ($get) => !empty($get('secondary_button_text'))),
                ]),

            Forms\Components\Section::make('Visual Design')
                ->schema([
                    Forms\Components\Select::make('layout')
                        ->label('Layout')
                        ->options([
                            'centered' => 'Centered',
                            'left-aligned' => 'Left Aligned',
                            'right-aligned' => 'Right Aligned',
                            'split' => 'Split Layout',
                        ])
                        ->default('centered'),

                    Forms\Components\Select::make('size')
                        ->label('Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                            'hero' => 'Hero Size',
                        ])
                        ->default('medium'),

                    Forms\Components\ColorPicker::make('background_color')
                        ->label('Background Color')
                        ->default('#F3F4F6'),

                    Forms\Components\ColorPicker::make('text_color')
                        ->label('Text Color')
                        ->default('#111827'),

                    Forms\Components\FileUpload::make('background_image')
                        ->label('Background Image')
                        ->image()
                        ->maxSize(5120),

                    Forms\Components\Select::make('background_overlay')
                        ->label('Background Overlay')
                        ->options([
                            'none' => 'None',
                            'light' => 'Light',
                            'medium' => 'Medium',
                            'dark' => 'Dark',
                        ])
                        ->default('none')
                        ->visible(fn ($get) => !empty($get('background_image'))),
                ]),

            Forms\Components\Section::make('Button Styling')
                ->schema([
                    Forms\Components\Select::make('button_style')
                        ->label('Primary Button Style')
                        ->options([
                            'solid' => 'Solid',
                            'outline' => 'Outline',
                            'ghost' => 'Ghost',
                            'gradient' => 'Gradient',
                        ])
                        ->default('solid'),

                    Forms\Components\ColorPicker::make('button_color')
                        ->label('Primary Button Color')
                        ->default('#3B82F6'),

                    Forms\Components\ColorPicker::make('button_text_color')
                        ->label('Primary Button Text Color')
                        ->default('#FFFFFF'),

                    Forms\Components\Select::make('button_size')
                        ->label('Button Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                            'xl' => 'Extra Large',
                        ])
                        ->default('medium'),

                    Forms\Components\Select::make('button_border_radius')
                        ->label('Button Border Radius')
                        ->options([
                            'none' => 'None',
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                            'full' => 'Full (Pill)',
                        ])
                        ->default('medium'),

                    Forms\Components\Toggle::make('button_shadow')
                        ->label('Button Shadow')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Animation & Effects')
                ->schema([
                    Forms\Components\Select::make('hover_effect')
                        ->label('Hover Effect')
                        ->options([
                            'none' => 'None',
                            'scale' => 'Scale',
                            'lift' => 'Lift',
                            'glow' => 'Glow',
                            'bounce' => 'Bounce',
                        ])
                        ->default('scale'),

                    Forms\Components\Toggle::make('pulse_effect')
                        ->label('Pulse Effect')
                        ->default(false),

                    Forms\Components\Select::make('entrance_animation')
                        ->label('Entrance Animation')
                        ->options([
                            'none' => 'None',
                            'fade-in' => 'Fade In',
                            'slide-up' => 'Slide Up',
                            'zoom-in' => 'Zoom In',
                        ])
                        ->default('none'),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'headline' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'button_text' => 'required|string|max:100',
            'button_url' => 'required|url',
            'open_in_new_tab' => 'boolean',
            'secondary_button_text' => 'nullable|string|max:100',
            'secondary_button_url' => 'nullable|url',
            'layout' => 'in:centered,left-aligned,right-aligned,split',
            'size' => 'in:small,medium,large,hero',
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'background_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'background_overlay' => 'in:none,light,medium,dark',
            'button_style' => 'in:solid,outline,ghost,gradient',
            'button_color' => 'nullable|string',
            'button_text_color' => 'nullable|string',
            'button_size' => 'in:small,medium,large,xl',
            'button_border_radius' => 'in:none,small,medium,large,full',
            'button_shadow' => 'boolean',
            'hover_effect' => 'in:none,scale,lift,glow,bounce',
            'pulse_effect' => 'boolean',
            'entrance_animation' => 'in:none,fade-in,slide-up,zoom-in',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $ctaClasses = ['cta-block'];
        $ctaClasses[] = 'layout-' . ($content['layout'] ?? 'centered');
        $ctaClasses[] = 'size-' . ($content['size'] ?? 'medium');

        if (!empty($content['entrance_animation']) && $content['entrance_animation'] !== 'none') {
            $ctaClasses[] = 'animate-' . $content['entrance_animation'];
        }

        if (!empty($content['pulse_effect'])) {
            $ctaClasses[] = 'pulse-effect';
        }

        $ctaStyles = [];
        
        if (!empty($content['background_color'])) {
            $ctaStyles[] = 'background-color: ' . $content['background_color'];
        }

        if (!empty($content['text_color'])) {
            $ctaStyles[] = 'color: ' . $content['text_color'];
        }

        if (!empty($content['background_image'])) {
            $ctaStyles[] = 'background-image: url(' . $content['background_image'] . ')';
            $ctaStyles[] = 'background-size: cover';
            $ctaStyles[] = 'background-position: center';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        $html .= '<div class="' . implode(' ', $ctaClasses) . '"';
        if (!empty($ctaStyles)) {
            $html .= ' style="' . implode('; ', $ctaStyles) . '"';
        }
        $html .= '>';

        // Background overlay
        if (!empty($content['background_image']) && !empty($content['background_overlay']) && $content['background_overlay'] !== 'none') {
            $html .= '<div class="cta-overlay overlay-' . $content['background_overlay'] . '"></div>';
        }

        $html .= '<div class="cta-content">';

        // Headline
        $html .= '<h2 class="cta-headline">' . e($content['headline']) . '</h2>';

        // Description
        if (!empty($content['description'])) {
            $html .= '<p class="cta-description">' . nl2br(e($content['description'])) . '</p>';
        }

        // Buttons
        $html .= '<div class="cta-buttons">';

        // Primary button
        $buttonClasses = ['cta-button', 'primary-button'];
        $buttonClasses[] = 'style-' . ($content['button_style'] ?? 'solid');
        $buttonClasses[] = 'size-' . ($content['button_size'] ?? 'medium');
        $buttonClasses[] = 'radius-' . ($content['button_border_radius'] ?? 'medium');

        if (!empty($content['button_shadow'])) {
            $buttonClasses[] = 'shadow';
        }

        if (!empty($content['hover_effect']) && $content['hover_effect'] !== 'none') {
            $buttonClasses[] = 'hover-' . $content['hover_effect'];
        }

        $buttonStyles = [];
        if (!empty($content['button_color'])) {
            $buttonStyles[] = 'background-color: ' . $content['button_color'];
            $buttonStyles[] = 'border-color: ' . $content['button_color'];
        }
        if (!empty($content['button_text_color'])) {
            $buttonStyles[] = 'color: ' . $content['button_text_color'];
        }

        $target = !empty($content['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '';
        
        $html .= '<a href="' . e($content['button_url']) . '" class="' . implode(' ', $buttonClasses) . '"';
        if (!empty($buttonStyles)) {
            $html .= ' style="' . implode('; ', $buttonStyles) . '"';
        }
        $html .= $target . '>' . e($content['button_text']) . '</a>';

        // Secondary button
        if (!empty($content['secondary_button_text']) && !empty($content['secondary_button_url'])) {
            $secondaryClasses = ['cta-button', 'secondary-button'];
            $secondaryClasses[] = 'size-' . ($content['button_size'] ?? 'medium');
            $secondaryClasses[] = 'radius-' . ($content['button_border_radius'] ?? 'medium');

            $html .= '<a href="' . e($content['secondary_button_url']) . '" class="' . implode(' ', $secondaryClasses) . '"' . $target . '>';
            $html .= e($content['secondary_button_text']) . '</a>';
        }

        $html .= '</div></div></div></div>';

        return $html;
    }
}
