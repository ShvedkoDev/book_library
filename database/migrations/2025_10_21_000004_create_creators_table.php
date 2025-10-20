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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('biography')->nullable();
            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->timestamps();

            $table->index(['name']);
            $table->fullText(['name', 'biography']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
