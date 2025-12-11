<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:export {--file=pages-export.json : The output file name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all CMS pages with their relationships to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Exporting CMS pages...');

        // Get all pages with their relationships
        $pages = Page::with(['resourceContributors', 'parent', 'children'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $exportData = [
            'export_date' => now()->toDateTimeString(),
            'total_pages' => $pages->count(),
            'pages' => $pages->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'custom_html_blocks' => $page->custom_html_blocks,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                    'is_published' => $page->is_published,
                    'show_in_navigation' => $page->show_in_navigation,
                    'is_homepage' => $page->is_homepage,
                    'published_at' => $page->published_at?->toDateTimeString(),
                    'order' => $page->order,
                    'parent_id' => $page->parent_id,
                    'parent_slug' => $page->parent?->slug,
                    'resource_contributors' => $page->resourceContributors->map(function ($contributor) use ($page) {
                        return [
                            'name' => $contributor->name,
                            'website_url' => $contributor->website_url,
                            'is_active' => $contributor->is_active,
                            'order' => $contributor->pivot->order ?? 0,
                        ];
                    })->toArray(),
                    'created_at' => $page->created_at->toDateTimeString(),
                    'updated_at' => $page->updated_at->toDateTimeString(),
                ];
            })->toArray(),
        ];

        // Save to storage
        $filename = $this->option('file');
        $path = storage_path('app/' . $filename);

        file_put_contents($path, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("✓ Exported {$pages->count()} pages to: {$path}");
        $this->line("  File size: " . $this->formatBytes(filesize($path)));

        return Command::SUCCESS;
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
