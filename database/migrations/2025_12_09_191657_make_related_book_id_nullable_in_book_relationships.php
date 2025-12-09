<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_relationships', function (Blueprint $table) {
            $table->dropForeign(['related_book_id']);
            $table->unsignedBigInteger('related_book_id')->nullable()->change();
            $table->foreign('related_book_id')->references('id')->on('books')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('book_relationships', function (Blueprint $table) {
            $table->dropForeign(['related_book_id']);
            $table->unsignedBigInteger('related_book_id')->nullable(false)->change();
            $table->foreign('related_book_id')->references('id')->on('books')->onDelete('cascade');
        });
    }
};
