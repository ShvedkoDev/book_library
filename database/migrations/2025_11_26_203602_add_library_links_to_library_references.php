<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('library_references', function (Blueprint $table) {
            // Add main_link field after catalog_link
            $table->string('main_link', 500)->nullable()
                  ->after('catalog_link')
                  ->comment('CSV: Direct link to book page in library catalog (Columns BH, BJ, BL, BN, BP)');

            // Add alt_link field after main_link
            $table->string('alt_link', 500)->nullable()
                  ->after('main_link')
                  ->comment('CSV: Alternative link if book not available - similar book (Columns BI, BK, BM, BO, BQ)');

            // Add index for link searches
            $table->index(['library_code', 'main_link'], 'idx_library_main_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_references', function (Blueprint $table) {
            $table->dropIndex('idx_library_main_link');
            $table->dropColumn(['main_link', 'alt_link']);
        });
    }
};
