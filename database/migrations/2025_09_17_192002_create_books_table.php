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
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('isbn13', 20)->nullable()->unique();
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->onDelete('set null');
            $table->foreignId('collection_id')->nullable()->constrained('collections')->onDelete('set null');
            $table->integer('publication_year')->nullable();
            $table->string('edition', 50)->nullable();
            $table->integer('pages')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('pdf_file', 500)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->enum('access_level', ['full', 'limited', 'unavailable'])->default('unavailable');
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['title']);
            $table->index(['language_id', 'is_active']);
            $table->index(['access_level', 'is_active']);
            $table->index(['publication_year']);
            $table->fullText(['title', 'subtitle', 'description']);
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
