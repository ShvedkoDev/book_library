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
        Schema::table('csv_imports', function (Blueprint $table) {
            // Add JSON fields for tracking book lists
            $table->json('created_log')->nullable()->after('error_log');
            $table->json('updated_log')->nullable()->after('created_log');
            $table->json('skipped_log')->nullable()->after('updated_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('csv_imports', function (Blueprint $table) {
            $table->dropColumn(['created_log', 'updated_log', 'skipped_log']);
        });
    }
};
