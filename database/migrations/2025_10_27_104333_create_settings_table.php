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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, boolean, integer, json
            $table->string('group')->default('general'); // general, library, email, etc.
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('key');
            $table->index('group');
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'library_email',
                'value' => 'library@example.com',
                'type' => 'string',
                'group' => 'library',
                'description' => 'Email address for library access requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_name',
                'value' => 'Micronesian Teachers Digital Library',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Name of the library site',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
