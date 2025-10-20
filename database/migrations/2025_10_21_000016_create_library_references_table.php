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
        Schema::create('library_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->string('library_code', 50)->comment('e.g., "UH", "COM"');
            $table->string('library_name', 255);
            $table->string('reference_number', 100)->nullable()->comment('CSV: UH/COM hard copy ref');
            $table->string('call_number', 100)->nullable()->comment('CSV: UH/COM hard copy call number');
            $table->string('catalog_link', 500)->nullable()->comment('CSV: UH hard copy link');
            $table->text('notes')->nullable()->comment('CSV: UH/COM note');
            $table->timestamps();

            $table->index(['book_id']);
            $table->index(['library_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_references');
    }
};
