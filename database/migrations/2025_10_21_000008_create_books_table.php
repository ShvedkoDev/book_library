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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // Identifiers
            $table->string('internal_id', 50)->unique()->nullable()->comment('CSV: ID - Internal unique ID');
            $table->string('palm_code', 100)->unique()->nullable()->comment('CSV: PALM code');

            // Basic Information
            $table->string('title', 500)->comment('CSV: Title');
            $table->string('subtitle', 500)->nullable()->comment('CSV: Sub-title');
            $table->string('translated_title', 500)->nullable()->comment('CSV: Translated-title');
            $table->enum('physical_type', ['book', 'journal', 'magazine', 'workbook', 'poster', 'other'])->nullable()->comment('CSV: Physical type');

            // Relationships
            $table->foreignId('collection_id')->nullable()->constrained('collections')->onDelete('set null');
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->onDelete('set null');

            // Publication Details
            $table->integer('publication_year')->nullable()->comment('CSV: Year');
            $table->integer('pages')->nullable()->comment('CSV: Pages');

            // Content
            $table->text('description')->nullable()->comment('CSV: ABSTRACT/DESCRIPTION');
            $table->text('toc')->nullable()->comment('CSV: TOC - Table of Contents');
            $table->text('notes_issue')->nullable()->comment('CSV: Notes related to the issue');
            $table->text('notes_content')->nullable()->comment('CSV: Notes related to content');

            // Contact & Availability
            $table->text('contact')->nullable()->comment('CSV: CONTACT - Hard copy ordering info');
            $table->enum('access_level', ['full', 'limited', 'unavailable'])->default('unavailable')->comment('CSV: UPLOADED');

            // Educational Standards
            $table->string('vla_standard', 255)->nullable()->comment('CSV: VLA standard');
            $table->string('vla_benchmark', 255)->nullable()->comment('CSV: VLA benchmark');

            // Status & Metadata
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['title']);
            $table->index(['access_level', 'is_active', 'publication_year'], 'idx_book_filters');
            $table->index(['internal_id', 'palm_code'], 'idx_identifiers');
            $table->index(['collection_id']);
            $table->index(['publisher_id']);
            $table->fullText(['title', 'subtitle', 'translated_title', 'description'], 'idx_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
