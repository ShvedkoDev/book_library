<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core reference tables
            LanguageSeeder::class,
            PublisherSeeder::class,
            CollectionSeeder::class,
            CreatorSeeder::class,
            ClassificationTypeSeeder::class,
            ClassificationValueSeeder::class,
            GeographicLocationSeeder::class,

            // User management
            UserSeeder::class,
            TermsOfUseSeeder::class,

            // Books with all relationships
            BookSeeder::class,
        ]);
    }
}
