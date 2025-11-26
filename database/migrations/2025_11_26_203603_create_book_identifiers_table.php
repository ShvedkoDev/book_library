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
        Schema::create('book_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');

            $table->enum('identifier_type', [
                'oclc',      // OCLC number (Column BR)
                'isbn',      // ISBN number (Column BS)
                'isbn13',    // ISBN-13 format
                'issn',      // For journals/magazines
                'doi',       // Digital Object Identifier
                'lccn',      // Library of Congress Control Number
                'other'      // Other number (Column BT)
            ])->comment('CSV: OLLC number (BR), ISBN number (BS), Other number (BT)');

            $table->string('identifier_value', 100)
                  ->comment('The actual identifier value');

            $table->text('notes')->nullable()
                  ->comment('Additional notes about this identifier');

            $table->timestamps();

            // Constraints and indexes
            $table->unique(['book_id', 'identifier_type', 'identifier_value'],
                          'unique_book_identifier');
            $table->index(['identifier_type', 'identifier_value'],
                         'idx_identifier_lookup');
            $table->index(['book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_identifiers');
    }
};
