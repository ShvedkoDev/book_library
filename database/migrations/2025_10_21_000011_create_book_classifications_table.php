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
        Schema::create('book_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('classification_value_id')->constrained('classification_values')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['book_id', 'classification_value_id'], 'unique_book_classification');
            $table->index(['book_id']);
            $table->index(['classification_value_id']);
            $table->index(['classification_value_id', 'book_id'], 'idx_classification_book');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_classifications');
    }
};
