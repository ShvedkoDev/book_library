<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Add notes_version field after notes_issue
            $table->text('notes_version')->nullable()
                  ->after('notes_issue')
                  ->comment('CSV: Notes related to version - version-specific notes');

            // Add abstract field after description
            $table->text('abstract')->nullable()
                  ->after('description')
                  ->comment('CSV: ABSTRACT - Single paragraph summary of the book');
        });

        // Update description field comment to reflect new structure
        DB::statement("ALTER TABLE books
                       MODIFY COLUMN description TEXT NULL
                       COMMENT 'CSV: DESCRIPTION - Single paragraph about the book'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['notes_version', 'abstract']);
        });

        // Restore original comment
        DB::statement("ALTER TABLE books
                       MODIFY COLUMN description TEXT NULL
                       COMMENT 'CSV: ABSTRACT/DESCRIPTION'");
    }
};
