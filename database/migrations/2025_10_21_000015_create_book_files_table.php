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
        Schema::create('book_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->enum('file_type', ['pdf', 'thumbnail', 'audio', 'video'])->comment('CSV: DOCUMENT/THUMBNAIL/Coupled audio/video');
            $table->string('file_path', 500)->comment('Full storage path');
            $table->string('filename', 255)->comment('Original filename');
            $table->bigInteger('file_size')->nullable()->comment('In bytes');
            $table->string('mime_type', 100)->nullable();
            $table->boolean('is_primary')->default(false)->comment('Primary vs alternative');
            $table->string('digital_source', 500)->nullable()->comment('CSV: DIGITAL SOURCE / ALTERNATIVE DIGITAL SOURCE');
            $table->string('external_url', 500)->nullable()->comment('For videos (YouTube/Vimeo links)');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['book_id', 'file_type', 'is_primary'], 'idx_file_type_primary');
            $table->index(['is_active']);
            $table->index(['book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_files');
    }
};
