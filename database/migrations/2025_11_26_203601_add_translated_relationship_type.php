<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'translated' to relationship_type enum
        DB::statement("ALTER TABLE book_relationships
                       MODIFY COLUMN relationship_type ENUM(
                           'same_version',
                           'same_language',
                           'supporting',
                           'other_language',
                           'translated',
                           'custom'
                       ) COMMENT 'CSV: Related (same), Related (omnibus), Related (support), Related (translated)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'translated' from enum
        // WARNING: This will fail if any records use 'translated' type
        DB::statement("ALTER TABLE book_relationships
                       MODIFY COLUMN relationship_type ENUM(
                           'same_version',
                           'same_language',
                           'supporting',
                           'other_language',
                           'custom'
                       ) COMMENT 'CSV: Related (same), Related (omnibus), Related (support), Related (different language)'");
    }
};
