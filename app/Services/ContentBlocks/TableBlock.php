<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class TableBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Table Block';
    }

    public function getDescription(): string
    {
        return 'Responsive data tables with customizable styling';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-table-cells';
    }

    public function getCategory(): string
    {
        return 'Content';
    }

    protected function getType(): string
    {
        return 'table';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Table Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Table Title')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Table Description')
                        ->maxLength(500)
                        ->rows(2),

                    Forms\Components\Repeater::make('headers')
                        ->label('Table Headers')
                        ->schema([
                            Forms\Components\TextInput::make('text')
                                ->label('Header Text')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\Select::make('alignment')
                                ->label('Alignment')
                                ->options([
                                    'left' => 'Left',
                                    'center' => 'Center',
                                    'right' => 'Right',
                                ])
                                ->default('left'),

                            Forms\Components\Toggle::make('sortable')
                                ->label('Sortable')
                                ->default(false),
                        ])
                        ->defaultItems(3)
                        ->addActionLabel('Add Header')
                        ->reorderable()
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('rows')
                        ->label('Table Rows')
                        ->schema([
                            Forms\Components\Repeater::make('cells')
                                ->label('Row Cells')
                                ->schema([
                                    Forms\Components\Textarea::make('content')
                                        ->label('Cell Content')
                                        ->required()
                                        ->rows(2),
                                ])
                                ->defaultItems(3)
                                ->addActionLabel('Add Cell')
                                ->simple()
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(3)
                        ->addActionLabel('Add Row')
                        ->reorderable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Table Styling')
                ->schema([
                    Forms\Components\Select::make('style')
                        ->label('Table Style')
                        ->options([
                            'default' => 'Default',
                            'striped' => 'Striped Rows',
                            'bordered' => 'Bordered',
                            'minimal' => 'Minimal',
                            'modern' => 'Modern',
                        ])
                        ->default('default'),

                    Forms\Components\Select::make('size')
                        ->label('Table Size')
                        ->options([
                            'compact' => 'Compact',
                            'normal' => 'Normal',
                            'comfortable' => 'Comfortable',
                        ])
                        ->default('normal'),

                    Forms\Components\ColorPicker::make('header_bg_color')
                        ->label('Header Background Color')
                        ->default('#F9FAFB'),

                    Forms\Components\ColorPicker::make('header_text_color')
                        ->label('Header Text Color')
                        ->default('#111827'),

                    Forms\Components\Toggle::make('responsive')
                        ->label('Responsive (Mobile Friendly)')
                        ->default(true),

                    Forms\Components\Toggle::make('hover_effect')
                        ->label('Row Hover Effect')
                        ->default(true),

                    Forms\Components\Toggle::make('show_caption')
                        ->label('Show Table Caption')
                        ->default(false),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'headers' => 'required|array|min:1',
            'headers.*.text' => 'required|string|max:100',
            'headers.*.alignment' => 'in:left,center,right',
            'headers.*.sortable' => 'boolean',
            'rows' => 'required|array|min:1',
            'rows.*.cells' => 'required|array',
            'rows.*.cells.*.content' => 'required|string',
            'style' => 'in:default,striped,bordered,minimal,modern',
            'size' => 'in:compact,normal,comfortable',
            'header_bg_color' => 'nullable|string',
            'header_text_color' => 'nullable|string',
            'responsive' => 'boolean',
            'hover_effect' => 'boolean',
            'show_caption' => 'boolean',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $tableClasses = ['table-block'];
        $tableClasses[] = 'style-' . ($content['style'] ?? 'default');
        $tableClasses[] = 'size-' . ($content['size'] ?? 'normal');

        if (!empty($content['responsive'])) {
            $tableClasses[] = 'responsive';
        }

        if (!empty($content['hover_effect'])) {
            $tableClasses[] = 'hover-effect';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['title'])) {
            $html .= '<h3 class="table-title">' . e($content['title']) . '</h3>';
        }

        if (!empty($content['description'])) {
            $html .= '<div class="table-description">' . nl2br(e($content['description'])) . '</div>';
        }

        $html .= '<div class="table-container">';
        $html .= '<table class="' . implode(' ', $tableClasses) . '">';

        // Table caption
        if (!empty($content['show_caption']) && !empty($content['title'])) {
            $html .= '<caption>' . e($content['title']) . '</caption>';
        }

        // Table header
        if (!empty($content['headers']) && is_array($content['headers'])) {
            $html .= '<thead>';
            $html .= '<tr>';

            $headerStyles = [];
            if (!empty($content['header_bg_color'])) {
                $headerStyles[] = 'background-color: ' . $content['header_bg_color'];
            }
            if (!empty($content['header_text_color'])) {
                $headerStyles[] = 'color: ' . $content['header_text_color'];
            }

            foreach ($content['headers'] as $header) {
                $thClasses = ['table-header'];
                $thClasses[] = 'align-' . ($header['alignment'] ?? 'left');

                if (!empty($header['sortable'])) {
                    $thClasses[] = 'sortable';
                }

                $html .= '<th class="' . implode(' ', $thClasses) . '"';
                if (!empty($headerStyles)) {
                    $html .= ' style="' . implode('; ', $headerStyles) . '"';
                }
                $html .= '>' . e($header['text']);

                if (!empty($header['sortable'])) {
                    $html .= '<span class="sort-icon"></span>';
                }

                $html .= '</th>';
            }

            $html .= '</tr></thead>';
        }

        // Table body
        if (!empty($content['rows']) && is_array($content['rows'])) {
            $html .= '<tbody>';

            foreach ($content['rows'] as $row) {
                $html .= '<tr>';

                if (!empty($row['cells']) && is_array($row['cells'])) {
                    foreach ($row['cells'] as $index => $cell) {
                        $tdClasses = ['table-cell'];
                        
                        // Inherit alignment from header if available
                        if (!empty($content['headers'][$index]['alignment'])) {
                            $tdClasses[] = 'align-' . $content['headers'][$index]['alignment'];
                        }

                        $html .= '<td class="' . implode(' ', $tdClasses) . '">';
                        $html .= nl2br(e($cell['content']));
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>';
        }

        $html .= '</table></div></div>';

        return $html;
    }
}
