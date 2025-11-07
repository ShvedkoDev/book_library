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
        Schema::create('csv_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // File Information
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Import Configuration
            $table->enum('mode', ['create_only', 'update_only', 'upsert', 'create_duplicates'])->default('upsert');
            $table->json('options')->nullable(); // Import options as JSON

            // Status and Progress
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('skipped_rows')->default(0);
            $table->integer('created_count')->default(0);
            $table->integer('updated_count')->default(0);

            // Results and Errors
            $table->longText('error_log')->nullable();
            $table->json('error_summary')->nullable();
            $table->longText('success_log')->nullable();
            $table->text('validation_errors')->nullable();

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_imports');
    }
};
