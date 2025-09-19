<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class DividerBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Divider Block';
    }

    public function getDescription(): string
    {
        return 'Visual separator with customizable styles';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-minus';
    }

    public function getCategory(): string
    {
        return 'Layout';
    }

    protected function getType(): string
    {
        return 'divider';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Divider Settings')
                ->schema([
                    Forms\Components\Select::make('style')
                        ->label('Divider Style')
                        ->options([
                            'solid' => 'Solid Line',
                            'dashed' => 'Dashed Line',
                            'dotted' => 'Dotted Line',
                            'double' => 'Double Line',
                            'gradient' => 'Gradient',
                            'decorative' => 'Decorative',
                        ])
                        ->default('solid'),

                    Forms\Components\Select::make('width')
                        ->label('Width')
                        ->options([
                            '25' => '25%',
                            '50' => '50%',
                            '75' => '75%',
                            '100' => '100%',
                        ])
                        ->default('100'),

                    Forms\Components\Select::make('thickness')
                        ->label('Thickness')
                        ->options([
                            'thin' => 'Thin (1px)',
                            'medium' => 'Medium (2px)',
                            'thick' => 'Thick (4px)',
                            'extra-thick' => 'Extra Thick (8px)',
                        ])
                        ->default('thin'),

                    Forms\Components\ColorPicker::make('color')
                        ->label('Color')
                        ->default('#E5E7EB'),

                    Forms\Components\Select::make('alignment')
                        ->label('Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ])
                        ->default('center'),

                    Forms\Components\TextInput::make('text')
                        ->label('Divider Text')
                        ->maxLength(100)
                        ->helperText('Optional text to display in the center'),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'style' => 'in:solid,dashed,dotted,double,gradient,decorative',
            'width' => 'in:25,50,75,100',
            'thickness' => 'in:thin,medium,thick,extra-thick',
            'color' => 'nullable|string',
            'alignment' => 'in:left,center,right',
            'text' => 'nullable|string|max:100',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $dividerClasses = ['divider-block'];
        $dividerClasses[] = 'style-' . ($content['style'] ?? 'solid');
        $dividerClasses[] = 'width-' . ($content['width'] ?? '100');
        $dividerClasses[] = 'thickness-' . ($content['thickness'] ?? 'thin');
        $dividerClasses[] = 'align-' . ($content['alignment'] ?? 'center');

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['text'])) {
            $html .= '<div class="' . implode(' ', $dividerClasses) . ' with-text">';
            $html .= '<span class="divider-text">' . e($content['text']) . '</span>';
            $html .= '</div>';
        } else {
            $dividerStyles = [];
            if (!empty($content['color'])) {
                $dividerStyles[] = 'border-color: ' . $content['color'];
            }

            $html .= '<hr class="' . implode(' ', $dividerClasses) . '"';
            if (!empty($dividerStyles)) {
                $html .= ' style="' . implode('; ', $dividerStyles) . '"';
            }
            $html .= '>';
        }

        $html .= '</div>';

        return $html;
    }
}
