<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\ResourceContributor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:import {--file=pages-export.json : The import file name} {--fresh : Delete existing pages before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CMS pages from a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->option('file');
        $path = storage_path('app/' . $filename);

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $this->info('Importing CMS pages...');

        $data = json_decode(file_get_contents($path), true);

        if (!$data || !isset($data['pages'])) {
            $this->error('Invalid import file format');
            return Command::FAILURE;
        }

        $this->line("Export date: {$data['export_date']}");
        $this->line("Total pages in file: {$data['total_pages']}");

        if ($this->option('fresh')) {
            if ($this->confirm('This will DELETE all existing pages. Are you sure?')) {
                $this->info('Deleting existing pages...');
                DB::table('page_resource_contributor')->delete();
                Page::query()->forceDelete();
                $this->info('✓ Existing pages deleted');
            } else {
                $this->info('Import cancelled');
                return Command::SUCCESS;
            }
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        // Create slug to ID mapping for parent relationships
        $slugToIdMap = [];

        // First pass: Create/update pages without parent relationships
        foreach ($data['pages'] as $pageData) {
            try {
                // Check if page exists by slug
                $existingPage = Page::withTrashed()->where('slug', $pageData['slug'])->first();

                if ($existingPage) {
                    // Update existing page
                    $existingPage->fill([
                        'title' => $pageData['title'],
                        'content' => $pageData['content'],
                        'custom_html_blocks' => $pageData['custom_html_blocks'],
                        'meta_description' => $pageData['meta_description'],
                        'meta_keywords' => $pageData['meta_keywords'],
                        'is_published' => $pageData['is_published'],
                        'show_in_navigation' => $pageData['show_in_navigation'],
                        'is_homepage' => $pageData['is_homepage'],
                        'published_at' => $pageData['published_at'],
                        'order' => $pageData['order'],
                    ]);

                    if ($existingPage->trashed()) {
                        $existingPage->restore();
                    }

                    $existingPage->save();
                    $slugToIdMap[$pageData['slug']] = $existingPage->id;
                    $updated++;
                    $this->line("  ↻ Updated: {$pageData['title']}");
                } else {
                    // Create new page
                    $page = Page::create([
                        'title' => $pageData['title'],
                        'slug' => $pageData['slug'],
                        'content' => $pageData['content'],
                        'custom_html_blocks' => $pageData['custom_html_blocks'],
                        'meta_description' => $pageData['meta_description'],
                        'meta_keywords' => $pageData['meta_keywords'],
                        'is_published' => $pageData['is_published'],
                        'show_in_navigation' => $pageData['show_in_navigation'],
                        'is_homepage' => $pageData['is_homepage'],
                        'published_at' => $pageData['published_at'],
                        'order' => $pageData['order'],
                    ]);

                    $slugToIdMap[$pageData['slug']] = $page->id;
                    $imported++;
                    $this->line("  ✓ Created: {$pageData['title']}");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to import: {$pageData['title']} - {$e->getMessage()}");
                $skipped++;
            }
        }

        // Second pass: Update parent relationships
        foreach ($data['pages'] as $pageData) {
            if ($pageData['parent_slug'] && isset($slugToIdMap[$pageData['parent_slug']])) {
                $page = Page::where('slug', $pageData['slug'])->first();
                if ($page) {
                    $page->parent_id = $slugToIdMap[$pageData['parent_slug']];
                    $page->save();
                }
            }
        }

        // Third pass: Attach resource contributors
        foreach ($data['pages'] as $pageData) {
            if (!empty($pageData['resource_contributors'])) {
                $page = Page::where('slug', $pageData['slug'])->first();
                if ($page) {
                    // Detach existing contributors
                    $page->resourceContributors()->detach();

                    // Attach new contributors
                    foreach ($pageData['resource_contributors'] as $contributorData) {
                        $contributor = ResourceContributor::firstOrCreate(
                            ['name' => $contributorData['name']],
                            [
                                'website_url' => $contributorData['website_url'],
                                'is_active' => $contributorData['is_active'],
                            ]
                        );

                        $page->resourceContributors()->attach($contributor->id, [
                            'order' => $contributorData['order'],
                        ]);
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Import completed!");
        $this->line("  Created: {$imported}");
        $this->line("  Updated: {$updated}");
        if ($skipped > 0) {
            $this->line("  Skipped: {$skipped}");
        }

        return Command::SUCCESS;
    }
}
