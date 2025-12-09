<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update collation for search fields to utf8mb4_unicode_ci (accent-insensitive)
        DB::statement("ALTER TABLE books MODIFY title VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::statement("ALTER TABLE books MODIFY description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        DB::statement("ALTER TABLE creators MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::statement("ALTER TABLE publishers MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Collections table might not have a title column, skip if it doesn't exist
        if (DB::getSchemaBuilder()->hasColumn('collections', 'name')) {
            DB::statement("ALTER TABLE collections MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to default collation
        DB::statement("ALTER TABLE books MODIFY title VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
        DB::statement("ALTER TABLE books MODIFY description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
        
        DB::statement("ALTER TABLE creators MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
        DB::statement("ALTER TABLE publishers MODIFY name VARCHAR(255) CHARACTER SET utf8mb4_0900_ai_ci");
        
        if (DB::getSchemaBuilder()->hasColumn('collections', 'name')) {
            DB::statement("ALTER TABLE collections MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
        }
    }
};
