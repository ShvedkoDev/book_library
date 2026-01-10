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
        Schema::table('books', function (Blueprint $table) {
            $table->string('program_partner_name', 255)->nullable()->after('publisher_id')
                  ->comment('CSV: Contributor / Project / Partner - The partner organization for this book');
        });

        // Migrate existing data from publishers.program_name to books.program_partner_name
        // This is a one-time migration to fix the data integrity issue
        DB::statement('
            UPDATE books b
            INNER JOIN publishers p ON b.publisher_id = p.id
            SET b.program_partner_name = p.program_name
            WHERE p.program_name IS NOT NULL AND p.program_name != ""
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('program_partner_name');
        });
    }
};
