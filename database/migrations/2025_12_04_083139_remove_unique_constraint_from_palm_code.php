<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove unique constraint from palm_code column because multiple editions
     * of the same book share the same palm_code but have different internal_ids.
     * For example, P011-a and P011-d both have palm_code TAW3.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique('books_palm_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unique('palm_code');
        });
    }
};
