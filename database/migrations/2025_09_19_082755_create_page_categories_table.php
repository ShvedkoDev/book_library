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
        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('cms_category_id')->constrained('cms_categories')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate relationships
            $table->unique(['page_id', 'cms_category_id']);

            // Indexes for performance
            $table->index('page_id');
            $table->index('cms_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_categories');
    }
};