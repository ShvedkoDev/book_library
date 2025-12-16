<?php

namespace App\Console\Commands;

use App\Models\Creator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeCreators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:merge-creators {source_id : The ID of the creator to merge FROM (will be deleted)} {target_id : The ID of the creator to merge TO (will be kept)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge two creators into one, moving all book relationships to the target creator.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceId = $this->argument('source_id');
        $targetId = $this->argument('target_id');

        if ($sourceId == $targetId) {
            $this->error('Source and target IDs cannot be the same.');
            return;
        }

        $source = Creator::find($sourceId);
        $target = Creator::find($targetId);

        if (!$source) {
            $this->error("Source creator with ID {$sourceId} not found.");
            return;
        }

        if (!$target) {
            $this->error("Target creator with ID {$targetId} not found.");
            return;
        }

        $this->info("Merging '{$source->name}' (ID: {$source->id}) into '{$target->name}' (ID: {$target->id})...");

        if (!$this->confirm('Are you sure you want to proceed? This action cannot be undone.')) {
            return;
        }

        DB::transaction(function () use ($source, $target) {
            // Get all book relationships for the source creator
            $sourceRelationships = DB::table('book_creators')->where('creator_id', $source->id)->get();

            $movedCount = 0;
            $skippedCount = 0;

            foreach ($sourceRelationships as $rel) {
                // Check if the target creator already has a relationship with this book and role
                $exists = DB::table('book_creators')
                    ->where('book_id', $rel->book_id)
                    ->where('creator_id', $target->id)
                    ->where('creator_type', $rel->creator_type)
                    ->exists();

                if ($exists) {
                    // If relationship already exists, just delete the source relationship (it's a duplicate)
                    DB::table('book_creators')->where('id', $rel->id)->delete();
                    $skippedCount++;
                } else {
                    // Move relationship to target
                    DB::table('book_creators')
                        ->where('id', $rel->id)
                        ->update(['creator_id' => $target->id]);
                    $movedCount++;
                }
            }

            // Delete the source creator
            $source->delete();

            $this->info("Successfully merged creators.");
            $this->info("- Moved {$movedCount} book relationships.");
            $this->info("- Skipped/Deleted {$skippedCount} duplicate relationships.");
            $this->info("- Deleted source creator '{$source->name}'.");
        });
    }
}
