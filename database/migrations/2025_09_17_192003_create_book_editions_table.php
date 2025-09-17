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
        Schema::create('book_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('edition_book_id')->constrained('books')->onDelete('cascade');
            $table->enum('edition_type', ['revised', 'updated', 'translated', 'abridged', 'other']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['parent_book_id', 'edition_book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_editions');
    }
};
