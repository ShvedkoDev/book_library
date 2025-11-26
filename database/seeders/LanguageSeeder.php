<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'chk',
                'name' => 'Chuukese',
                'native_name' => 'Chuuk',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'kos',
                'name' => 'Kosraean',
                'native_name' => 'Kosrae',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pon',
                'name' => 'Pohnpeian',
                'native_name' => 'Pohnpei',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'uli',
                'name' => 'Ulithian',
                'native_name' => 'Ulithi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'woe',
                'name' => 'Woleaian',
                'native_name' => 'Woleai',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'yap',
                'name' => 'Yapese',
                'native_name' => 'Waqab',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('languages')->insert($languages);
    }
}
