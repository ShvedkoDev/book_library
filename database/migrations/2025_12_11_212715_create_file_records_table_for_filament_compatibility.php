<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates an empty file_records table for Filament bulk action compatibility.
     * This table exists only to prevent query errors - actual data is stored in the filesystem.
     */
    public function up(): void
    {
        Schema::create('file_records', function (Blueprint $table) {
            $table->string('id')->primary();
            // No other columns needed - this is just a dummy table for Filament
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_records');
    }
};
