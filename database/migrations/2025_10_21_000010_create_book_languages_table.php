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
        Schema::create('book_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('Distinguishes Language 1 vs Language 2');
            $table->timestamps();

            $table->unique(['book_id', 'language_id']);
            $table->index(['book_id']);
            $table->index(['language_id', 'is_primary'], 'idx_language_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_languages');
    }
};
