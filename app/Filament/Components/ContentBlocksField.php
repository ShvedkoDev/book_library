<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use App\Services\ContentBlockManager;
use Closure;

class ContentBlocksField extends Field
{
    protected string $view = 'filament.components.content-blocks-field';

    protected ContentBlockManager $blockManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blockManager = ContentBlockManager::getInstance();
        
        $this->schema([
            Repeater::make($this->getName())
                ->schema([
                    Group::make([
                        Select::make('block_type')
                            ->label('Block Type')
                            ->options($this->getBlockOptions())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $defaultSettings = $this->blockManager->getDefaultSettings($state);
                                    $set('settings', $defaultSettings);
                                }
                            }),

                        Select::make('block_category')
                            ->label('Category')
                            ->options($this->getBlockCategories())
                            ->visible(false), // Hidden, used for filtering
                    ])
                    ->columns(2),

                    Section::make('Content')
                        ->schema(function ($get) {
                            $blockType = $get('block_type');
                            if (!$blockType) {
                                return [];
                            }

                            return $this->blockManager->getFormSchema($blockType);
                        })
                        ->visible(fn ($get) => !empty($get('block_type')))
                        ->collapsible()
                        ->collapsed(false),

                    Section::make('Block Settings')
                        ->schema(function ($get) {
                            $blockType = $get('block_type');
                            if (!$blockType) {
                                return [];
                            }

                            $block = $this->blockManager->getBlock($blockType);
                            if ($block && method_exists($block, 'getSettingsSchema')) {
                                return $block->getSettingsSchema();
                            }

                            return [];
                        })
                        ->visible(fn ($get) => !empty($get('block_type')))
                        ->collapsible()
                        ->collapsed(true),
                ])
                ->addActionLabel('Add Content Block')
                ->reorderable()
                ->collapsible()
                ->cloneable()
                ->deleteAction(function ($action) {
                    return $action->requiresConfirmation();
                })
                ->defaultItems(0)
                ->columnSpanFull()
        ]);
    }

    protected function getBlockOptions(): array
    {
        return $this->blockManager->getBlockOptionsForSelect();
    }

    protected function getBlockCategories(): array
    {
        return $this->blockManager->getBlockCategoriesForSelect();
    }

    public function enablePreview(bool $condition = true): static
    {
        $this->evaluate($condition) ? $this->enablePreviewMode() : $this->disablePreviewMode();

        return $this;
    }

    protected function enablePreviewMode(): void
    {
        $this->view = 'filament.components.content-blocks-field-with-preview';
    }

    protected function disablePreviewMode(): void
    {
        $this->view = 'filament.components.content-blocks-field';
    }

    public function filterByCategory(string|array $categories): static
    {
        $categories = is_array($categories) ? $categories : [$categories];
        
        $this->modifySchema(function (array $schema) use ($categories) {
            // Filter block options based on categories
            $filteredOptions = [];
            $allBlocks = $this->blockManager->getAvailableBlocks();
            
            foreach ($allBlocks as $type => $block) {
                if (in_array($block['category'], $categories)) {
                    $filteredOptions[$type] = $block['name'];
                }
            }
            
            // Update the select options
            if (isset($schema[0])) {
                $repeater = $schema[0];
                if (method_exists($repeater, 'getChildComponents')) {
                    foreach ($repeater->getChildComponents() as $component) {
                        if ($component instanceof Group) {
                            foreach ($component->getChildComponents() as $groupComponent) {
                                if ($groupComponent instanceof Select && $groupComponent->getName() === 'block_type') {
                                    $groupComponent->options($filteredOptions);
                                }
                            }
                        }
                    }
                }
            }
            
            return $schema;
        });

        return $this;
    }

    public function maxBlocks(int $max): static
    {
        $this->modifySchema(function (array $schema) use ($max) {
            if (isset($schema[0]) && method_exists($schema[0], 'maxItems')) {
                $schema[0]->maxItems($max);
            }
            return $schema;
        });

        return $this;
    }

    public function minBlocks(int $min): static
    {
        $this->modifySchema(function (array $schema) use ($min) {
            if (isset($schema[0]) && method_exists($schema[0], 'minItems')) {
                $schema[0]->minItems($min);
            }
            return $schema;
        });

        return $this;
    }

    public function defaultBlocks(array $blocks): static
    {
        $this->modifySchema(function (array $schema) use ($blocks) {
            if (isset($schema[0]) && method_exists($schema[0], 'default')) {
                $schema[0]->default($blocks);
            }
            return $schema;
        });

        return $this;
    }

    protected function modifySchema(Closure $callback): void
    {
        $currentSchema = $this->getSchema();
        $modifiedSchema = $callback($currentSchema);
        $this->schema($modifiedSchema);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }
}
