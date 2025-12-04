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
     * Convert books.physical_type from enum to foreign key referencing physical_types table.
     * This migration assumes physical_types table exists and is populated.
     */
    public function up(): void
    {
        // Step 1: Add temporary column to store current physical_type values
        Schema::table('books', function (Blueprint $table) {
            $table->string('physical_type_temp', 100)->nullable()->after('physical_type');
        });

        // Step 2: Copy current enum values to temp column
        DB::statement('UPDATE books SET physical_type_temp = physical_type');

        // Step 3: Drop the old enum column
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('physical_type');
        });

        // Step 4: Add new foreign key column
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('physical_type_id')->nullable()->after('translated_title')
                ->constrained('physical_types')->onDelete('set null')
                ->comment('Physical format type');
        });

        // Step 5: Map old enum values to physical_types IDs
        // Get all books with physical_type_temp values
        $books = DB::table('books')->whereNotNull('physical_type_temp')->get();

        foreach ($books as $book) {
            $oldValue = $book->physical_type_temp;

            // Map old enum values to new physical type names
            // Old enum: book, journal, magazine, workbook, poster, other
            // New types: Booklet, CD-ROM, Comic, Textbook
            $mapping = [
                'book' => 'Booklet',
                'journal' => 'Journal',
                'magazine' => 'Magazine',
                'workbook' => 'Workbook',
                'poster' => 'Poster',
                'other' => 'CD-ROM', // Map 'other' to CD-ROM as most "other" were CD-ROMs
            ];

            $physicalTypeName = $mapping[$oldValue] ?? 'Booklet';

            // Get or create the physical type
            $physicalType = DB::table('physical_types')->where('name', $physicalTypeName)->first();

            if (!$physicalType) {
                // Create if doesn't exist
                $slug = strtolower(str_replace([' ', '_'], '-', $physicalTypeName));
                DB::table('physical_types')->insert([
                    'name' => $physicalTypeName,
                    'slug' => $slug,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $physicalType = DB::table('physical_types')->where('name', $physicalTypeName)->first();
            }

            // Update book with physical_type_id
            DB::table('books')->where('id', $book->id)->update([
                'physical_type_id' => $physicalType->id,
            ]);
        }

        // Step 6: Drop temporary column
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('physical_type_temp');
        });

        // Step 7: Add additional physical types that might be needed
        $additionalTypes = [
            ['name' => 'Journal', 'slug' => 'journal'],
            ['name' => 'Magazine', 'slug' => 'magazine'],
            ['name' => 'Workbook', 'slug' => 'workbook'],
            ['name' => 'Poster', 'slug' => 'poster'],
        ];

        foreach ($additionalTypes as $type) {
            $exists = DB::table('physical_types')->where('name', $type['name'])->exists();
            if (!$exists) {
                DB::table('physical_types')->insert([
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back the enum column
        Schema::table('books', function (Blueprint $table) {
            $table->enum('physical_type', ['book', 'journal', 'magazine', 'workbook', 'poster', 'other'])
                ->nullable()->after('translated_title');
        });

        // Step 2: Copy physical_type_id back to enum (simplified - all become 'book')
        DB::statement("UPDATE books SET physical_type = 'book' WHERE physical_type_id IS NOT NULL");

        // Step 3: Drop the foreign key column
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['physical_type_id']);
            $table->dropColumn('physical_type_id');
        });
    }
};
