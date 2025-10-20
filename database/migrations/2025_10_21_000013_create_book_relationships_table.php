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
        Schema::create('book_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('related_book_id')->constrained('books')->onDelete('cascade');
            $table->enum('relationship_type', ['same_version', 'same_language', 'supporting', 'other_language', 'custom'])->comment('CSV: Related (same), Related (omnibus), Related (support), Related (different language)');
            $table->string('relationship_code', 50)->nullable()->comment('For grouping related books');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'related_book_id', 'relationship_type'], 'unique_book_relationship');
            $table->index(['book_id', 'relationship_type'], 'idx_book_rel_type');
            $table->index(['relationship_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_relationships');
    }
};
