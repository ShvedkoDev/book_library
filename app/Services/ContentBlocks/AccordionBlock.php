<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class AccordionBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Accordion Block';
    }

    public function getDescription(): string
    {
        return 'Collapsible content sections with expand/collapse functionality';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-bars-3-bottom-right';
    }

    public function getCategory(): string
    {
        return 'Interactive';
    }

    protected function getType(): string
    {
        return 'accordion';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Accordion Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Accordion Title')
                        ->maxLength(255),

                    Forms\Components\Repeater::make('items')
                        ->label('Accordion Items')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Item Title')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\RichEditor::make('content')
                                ->label('Item Content')
                                ->required()
                                ->columnSpanFull()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'bulletList',
                                    'orderedList',
                                ]),

                            Forms\Components\Toggle::make('open_by_default')
                                ->label('Open by Default')
                                ->default(false),

                            Forms\Components\FileUpload::make('icon')
                                ->label('Custom Icon')
                                ->image()
                                ->maxSize(1024)
                                ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/gif']),
                        ])
                        ->defaultItems(3)
                        ->addActionLabel('Add Accordion Item')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Accordion Settings')
                ->schema([
                    Forms\Components\Select::make('behavior')
                        ->label('Accordion Behavior')
                        ->options([
                            'single' => 'Only One Open at a Time',
                            'multiple' => 'Multiple Can Be Open',
                        ])
                        ->default('single'),

                    Forms\Components\Select::make('style')
                        ->label('Accordion Style')
                        ->options([
                            'default' => 'Default',
                            'bordered' => 'Bordered',
                            'minimal' => 'Minimal',
                            'card' => 'Card Style',
                            'modern' => 'Modern',
                        ])
                        ->default('default'),

                    Forms\Components\Select::make('animation')
                        ->label('Animation Style')
                        ->options([
                            'slide' => 'Slide',
                            'fade' => 'Fade',
                            'none' => 'No Animation',
                        ])
                        ->default('slide'),

                    Forms\Components\TextInput::make('animation_duration')
                        ->label('Animation Duration (ms)')
                        ->numeric()
                        ->default(300)
                        ->minValue(100)
                        ->maxValue(1000),

                    Forms\Components\ColorPicker::make('header_color')
                        ->label('Header Background Color')
                        ->default('#F9FAFB'),

                    Forms\Components\ColorPicker::make('header_text_color')
                        ->label('Header Text Color')
                        ->default('#111827'),

                    Forms\Components\ColorPicker::make('content_color')
                        ->label('Content Background Color')
                        ->default('#FFFFFF'),

                    Forms\Components\Toggle::make('show_icons')
                        ->label('Show Expand/Collapse Icons')
                        ->default(true),

                    Forms\Components\Select::make('icon_position')
                        ->label('Icon Position')
                        ->options([
                            'left' => 'Left',
                            'right' => 'Right',
                        ])
                        ->default('right')
                        ->visible(fn ($get) => $get('show_icons')),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.content' => 'required|string',
            'items.*.open_by_default' => 'boolean',
            'items.*.icon' => 'nullable|file|mimes:svg,png,gif|max:1024',
            'behavior' => 'in:single,multiple',
            'style' => 'in:default,bordered,minimal,card,modern',
            'animation' => 'in:slide,fade,none',
            'animation_duration' => 'nullable|numeric|min:100|max:1000',
            'header_color' => 'nullable|string',
            'header_text_color' => 'nullable|string',
            'content_color' => 'nullable|string',
            'show_icons' => 'boolean',
            'icon_position' => 'in:left,right',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $accordionClasses = ['accordion-block'];
        $accordionClasses[] = 'behavior-' . ($content['behavior'] ?? 'single');
        $accordionClasses[] = 'style-' . ($content['style'] ?? 'default');
        $accordionClasses[] = 'animation-' . ($content['animation'] ?? 'slide');

        if (!empty($content['show_icons'])) {
            $accordionClasses[] = 'with-icons';
            $accordionClasses[] = 'icons-' . ($content['icon_position'] ?? 'right');
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['title'])) {
            $html .= '<h3 class="accordion-title">' . e($content['title']) . '</h3>';
        }

        $accordionId = 'accordion-' . uniqid();
        $html .= '<div class="' . implode(' ', $accordionClasses) . '" id="' . $accordionId . '"';
        
        $accordionData = [];
        $accordionData[] = 'data-behavior="' . ($content['behavior'] ?? 'single') . '"';
        $accordionData[] = 'data-animation="' . ($content['animation'] ?? 'slide') . '"';
        $accordionData[] = 'data-duration="' . ($content['animation_duration'] ?? 300) . '"';
        
        $html .= ' ' . implode(' ', $accordionData) . '>';

        if (!empty($content['items']) && is_array($content['items'])) {
            foreach ($content['items'] as $index => $item) {
                $itemId = $accordionId . '-item-' . $index;
                $isOpen = !empty($item['open_by_default']);

                $html .= '<div class="accordion-item' . ($isOpen ? ' open' : '') . '">';

                // Accordion header
                $headerStyles = [];
                if (!empty($content['header_color'])) {
                    $headerStyles[] = 'background-color: ' . $content['header_color'];
                }
                if (!empty($content['header_text_color'])) {
                    $headerStyles[] = 'color: ' . $content['header_text_color'];
                }

                $html .= '<button type="button" class="accordion-header" ';
                $html .= 'aria-expanded="' . ($isOpen ? 'true' : 'false') . '" ';
                $html .= 'aria-controls="' . $itemId . '" ';
                $html .= 'data-target="' . $itemId . '"';
                
                if (!empty($headerStyles)) {
                    $html .= ' style="' . implode('; ', $headerStyles) . '"';
                }
                
                $html .= '>';

                // Custom icon or default
                if (!empty($content['show_icons'])) {
                    if (!empty($item['icon'])) {
                        $html .= '<img src="' . e($item['icon']) . '" alt="" class="accordion-custom-icon">';
                    } else {
                        $html .= '<span class="accordion-default-icon"></span>';
                    }
                }

                $html .= '<span class="accordion-header-text">' . e($item['title']) . '</span>';

                if (!empty($content['show_icons'])) {
                    $html .= '<span class="accordion-toggle-icon"></span>';
                }

                $html .= '</button>';

                // Accordion content
                $contentStyles = [];
                if (!empty($content['content_color'])) {
                    $contentStyles[] = 'background-color: ' . $content['content_color'];
                }

                $html .= '<div class="accordion-content" id="' . $itemId . '"';
                if (!empty($contentStyles)) {
                    $html .= ' style="' . implode('; ', $contentStyles) . '"';
                }
                $html .= '>';

                $html .= '<div class="accordion-content-inner">';
                $html .= $item['content'];
                $html .= '</div></div></div>';
            }
        }

        $html .= '</div></div>';

        return $html;
    }
}
