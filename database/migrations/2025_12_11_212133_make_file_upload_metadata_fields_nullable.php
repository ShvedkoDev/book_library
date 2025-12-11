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
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('original_name')->nullable()->change();
            $table->string('file_name')->nullable()->change();
            $table->string('mime_type')->nullable()->change();
            $table->unsignedBigInteger('file_size')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('original_name')->nullable(false)->change();
            $table->string('file_name')->nullable(false)->change();
            $table->string('mime_type')->nullable(false)->change();
            $table->unsignedBigInteger('file_size')->nullable(false)->change();
        });
    }
};
