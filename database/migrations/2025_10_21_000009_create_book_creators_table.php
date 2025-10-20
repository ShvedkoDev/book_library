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
        Schema::create('book_creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
            $table->enum('creator_type', ['author', 'illustrator', 'editor', 'translator', 'contributor']);
            $table->string('role_description', 100)->nullable()->comment('For custom roles like "Other creator ROLE"');
            $table->integer('sort_order')->default(0)->comment('Maintains sequence (Author2, Author3, Illustrator1-5)');
            $table->timestamps();

            $table->unique(['book_id', 'creator_id', 'creator_type', 'role_description'], 'unique_book_creator');
            $table->index(['book_id']);
            $table->index(['creator_id']);
            $table->index(['creator_type']);
            $table->index(['book_id', 'creator_type', 'sort_order'], 'idx_creator_type_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_creators');
    }
};
