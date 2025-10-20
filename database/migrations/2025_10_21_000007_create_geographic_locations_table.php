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
        Schema::create('geographic_locations', function (Blueprint $table) {
            $table->id();
            $table->enum('location_type', ['island', 'state', 'region']);
            $table->string('name', 100);
            $table->foreignId('parent_id')->nullable()->constrained('geographic_locations')->onDelete('cascade')->comment('State > Island hierarchy');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['location_type', 'name', 'parent_id'], 'unique_geographic_location');
            $table->index(['location_type', 'is_active']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geographic_locations');
    }
};
