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
        Schema::create('filter_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('filter_type'); // e.g., 'purpose', 'learner_level', 'language', 'type'
            $table->string('filter_value'); // e.g., 'Math', 'Grade 1', 'Chuukese'
            $table->string('filter_slug')->nullable(); // slug of the filter value
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for analytics
            $table->index(['filter_type', 'filter_value']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_analytics');
    }
};
