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
        Schema::create('classification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('e.g., Purpose, Genre, Type, Learner Level');
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('allow_multiple')->default(true)->comment('Can book have multiple values?');
            $table->boolean('use_for_filtering')->default(true)->comment('Show in filter UI?');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_types');
    }
};
