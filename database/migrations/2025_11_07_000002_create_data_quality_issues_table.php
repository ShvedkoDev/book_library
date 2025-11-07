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
        Schema::create('data_quality_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('csv_import_id')->nullable()->constrained()->onDelete('set null');
            $table->string('issue_type'); // missing_title, invalid_access_level, missing_language, etc.
            $table->enum('severity', ['critical', 'warning', 'info'])->default('warning');
            $table->string('field_name')->nullable(); // Which field has the issue
            $table->text('message'); // Human-readable description
            $table->json('context')->nullable(); // Additional context (expected vs actual values, etc.)
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('book_id');
            $table->index('csv_import_id');
            $table->index('issue_type');
            $table->index('severity');
            $table->index('is_resolved');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_quality_issues');
    }
};
