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
        Schema::table('books', function (Blueprint $table) {
            // Add foreign key to track which book this was duplicated from
            $table->foreignId('duplicated_from_book_id')
                ->nullable()
                ->after('id')
                ->constrained('books')
                ->onDelete('set null')
                ->comment('ID of the book this was duplicated from');

            // Add timestamp for when duplication occurred
            $table->timestamp('duplicated_at')
                ->nullable()
                ->after('duplicated_from_book_id')
                ->comment('When this book was duplicated');

            // Add index for faster queries
            $table->index('duplicated_from_book_id', 'idx_duplicated_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['duplicated_from_book_id']);
            $table->dropIndex('idx_duplicated_from');
            $table->dropColumn(['duplicated_from_book_id', 'duplicated_at']);
        });
    }
};
