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
        Schema::create('page_resource_contributor', function (Blueprint $table) {
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('resource_contributor_id')->constrained('resource_contributors')->onDelete('cascade');
            $table->integer('order')->default(0);

            // Composite primary key
            $table->primary(['page_id', 'resource_contributor_id'], 'page_contributor_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_resource_contributor');
    }
};
