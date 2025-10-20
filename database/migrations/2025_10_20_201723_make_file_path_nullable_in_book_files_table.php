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
        Schema::table('book_files', function (Blueprint $table) {
            // Make file_path and filename nullable since video files use external_url instead
            $table->string('file_path', 500)->nullable()->change();
            $table->string('filename', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_files', function (Blueprint $table) {
            // Revert to NOT NULL (be careful if data exists with null values)
            $table->string('file_path', 500)->nullable(false)->change();
            $table->string('filename', 255)->nullable(false)->change();
        });
    }
};
