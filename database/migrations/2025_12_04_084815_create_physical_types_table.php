<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create physical_types table to replace the enum in books table.
     * This allows dynamic addition of new physical types from CSV imports.
     */
    public function up(): void
    {
        // Create physical_types table
        Schema::create('physical_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Physical type name (e.g., Booklet, CD-ROM, Comic, Textbook)');
            $table->string('slug', 100)->unique()->comment('URL-friendly slug');
            $table->text('description')->nullable()->comment('Description of the physical type');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        // Seed with initial physical types from CSV
        $physicalTypes = [
            ['name' => 'Booklet', 'slug' => 'booklet', 'sort_order' => 1],
            ['name' => 'CD-ROM', 'slug' => 'cd-rom', 'sort_order' => 2],
            ['name' => 'Comic', 'slug' => 'comic', 'sort_order' => 3],
            ['name' => 'Textbook', 'slug' => 'textbook', 'sort_order' => 4],
        ];

        foreach ($physicalTypes as $type) {
            DB::table('physical_types')->insert([
                'name' => $type['name'],
                'slug' => $type['slug'],
                'sort_order' => $type['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_types');
    }
};
