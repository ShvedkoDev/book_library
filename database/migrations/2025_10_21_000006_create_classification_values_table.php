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
        Schema::create('classification_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classification_type_id')->constrained('classification_types')->onDelete('cascade');
            $table->string('value', 100);
            $table->foreignId('parent_id')->nullable()->constrained('classification_values')->onDelete('cascade')->comment('For hierarchical values (Genre > Sub-genre)');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['classification_type_id', 'value', 'parent_id'], 'unique_classification_value');
            $table->index(['classification_type_id', 'is_active']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_values');
    }
};
