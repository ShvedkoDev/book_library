<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE book_relationships MODIFY relationship_type ENUM('same_version', 'same_language', 'supporting', 'other_language', 'translated', 'omnibus', 'custom')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE book_relationships MODIFY relationship_type ENUM('same_version', 'same_language', 'supporting', 'other_language', 'translated', 'custom')");
    }
};
