<?php

namespace App\Services\ContentBlocks;

use App\Services\ContentBlocks\Contracts\ContentBlockInterface;
use Illuminate\Support\Facades\View;
use Filament\Forms;

abstract class AbstractContentBlock implements ContentBlockInterface
{
    public function getDefaultSettings(): array
    {
        return [
            'alignment' => 'left',
            'margin_top' => 'medium',
            'margin_bottom' => 'medium',
            'padding_top' => 'none',
            'padding_bottom' => 'none',
            'background_color' => '',
            'text_color' => '',
            'custom_css_class' => '',
            'animation' => 'none',
            'responsive_settings' => [
                'mobile' => ['hidden' => false],
                'tablet' => ['hidden' => false],
                'desktop' => ['hidden' => false],
            ],
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            Forms\Components\Section::make('Layout Settings')
                ->schema([
                    Forms\Components\Select::make('alignment')
                        ->label('Content Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                            'justify' => 'Justify',
                        ])
                        ->default('left'),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('margin_top')
                                ->label('Top Margin')
                                ->options([
                                    'none' => 'None',
                                    'small' => 'Small',
                                    'medium' => 'Medium',
                                    'large' => 'Large',
                                    'xlarge' => 'Extra Large',
                                ])
                                ->default('medium'),

                            Forms\Components\Select::make('margin_bottom')
                                ->label('Bottom Margin')
                                ->options([
                                    'none' => 'None',
                                    'small' => 'Small',
                                    'medium' => 'Medium',
                                    'large' => 'Large',
                                    'xlarge' => 'Extra Large',
                                ])
                                ->default('medium'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('padding_top')
                                ->label('Top Padding')
                                ->options([
                                    'none' => 'None',
                                    'small' => 'Small',
                                    'medium' => 'Medium',
                                    'large' => 'Large',
                                    'xlarge' => 'Extra Large',
                                ])
                                ->default('none'),

                            Forms\Components\Select::make('padding_bottom')
                                ->label('Bottom Padding')
                                ->options([
                                    'none' => 'None',
                                    'small' => 'Small',
                                    'medium' => 'Medium',
                                    'large' => 'Large',
                                    'xlarge' => 'Extra Large',
                                ])
                                ->default('none'),
                        ]),
                ]),

            Forms\Components\Section::make('Styling')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\ColorPicker::make('background_color')
                                ->label('Background Color')
                                ->alpha(),

                            Forms\Components\ColorPicker::make('text_color')
                                ->label('Text Color')
                                ->alpha(),
                        ]),

                    Forms\Components\TextInput::make('custom_css_class')
                        ->label('Custom CSS Class')
                        ->helperText('Additional CSS classes for custom styling'),

                    Forms\Components\Select::make('animation')
                        ->label('Animation')
                        ->options([
                            'none' => 'None',
                            'fade-in' => 'Fade In',
                            'slide-up' => 'Slide Up',
                            'slide-down' => 'Slide Down',
                            'slide-left' => 'Slide Left',
                            'slide-right' => 'Slide Right',
                            'zoom-in' => 'Zoom In',
                            'bounce' => 'Bounce',
                        ])
                        ->default('none'),
                ]),

            Forms\Components\Section::make('Responsive Settings')
                ->collapsible()
                ->schema([
                    Forms\Components\Fieldset::make('Mobile')
                        ->schema([
                            Forms\Components\Toggle::make('responsive_settings.mobile.hidden')
                                ->label('Hide on Mobile')
                                ->default(false),
                        ]),

                    Forms\Components\Fieldset::make('Tablet')
                        ->schema([
                            Forms\Components\Toggle::make('responsive_settings.tablet.hidden')
                                ->label('Hide on Tablet')
                                ->default(false),
                        ]),

                    Forms\Components\Fieldset::make('Desktop')
                        ->schema([
                            Forms\Components\Toggle::make('responsive_settings.desktop.hidden')
                                ->label('Hide on Desktop')
                                ->default(false),
                        ]),
                ]),
        ];
    }

    public function validateData(array $data): array
    {
        $rules = $this->getValidationRules();
        
        if (empty($rules)) {
            return ['valid' => true];
        }

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        return ['valid' => true];
    }

    protected function generateBlockClasses(array $settings): string
    {
        $classes = ['content-block'];

        // Add alignment class
        if (!empty($settings['alignment'])) {
            $classes[] = 'text-' . $settings['alignment'];
        }

        // Add margin classes
        if (!empty($settings['margin_top'])) {
            $classes[] = 'mt-' . $settings['margin_top'];
        }

        if (!empty($settings['margin_bottom'])) {
            $classes[] = 'mb-' . $settings['margin_bottom'];
        }

        // Add padding classes
        if (!empty($settings['padding_top'])) {
            $classes[] = 'pt-' . $settings['padding_top'];
        }

        if (!empty($settings['padding_bottom'])) {
            $classes[] = 'pb-' . $settings['padding_bottom'];
        }

        // Add animation classes
        if (!empty($settings['animation']) && $settings['animation'] !== 'none') {
            $classes[] = 'animate-' . $settings['animation'];
        }

        // Add responsive classes
        if (!empty($settings['responsive_settings'])) {
            foreach ($settings['responsive_settings'] as $breakpoint => $config) {
                if (!empty($config['hidden'])) {
                    $classes[] = 'hidden-' . $breakpoint;
                }
            }
        }

        // Add custom CSS class
        if (!empty($settings['custom_css_class'])) {
            $classes[] = $settings['custom_css_class'];
        }

        return implode(' ', $classes);
    }

    protected function generateBlockStyles(array $settings): string
    {
        $styles = [];

        if (!empty($settings['background_color'])) {
            $styles[] = 'background-color: ' . $settings['background_color'];
        }

        if (!empty($settings['text_color'])) {
            $styles[] = 'color: ' . $settings['text_color'];
        }

        return implode('; ', $styles);
    }

    protected function renderView(string $view, array $data = []): string
    {
        if (!View::exists($view)) {
            return '<div class="alert alert-warning">Template not found: ' . $view . '</div>';
        }

        return View::make($view, $data)->render();
    }

    public function getPreviewTemplate(): string
    {
        return 'cms.blocks.preview.' . $this->getType();
    }

    abstract protected function getType(): string;
}
