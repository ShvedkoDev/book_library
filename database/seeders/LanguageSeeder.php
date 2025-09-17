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
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'chuukese',
                'name' => 'Chuukese',
                'native_name' => 'Chuuk',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pohnpeian',
                'name' => 'Pohnpeian',
                'native_name' => 'Pohnpei',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'yapese',
                'name' => 'Yapese',
                'native_name' => 'Waqab',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'kosraean',
                'name' => 'Kosraean',
                'native_name' => 'Kosrae',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'marshallese',
                'name' => 'Marshallese',
                'native_name' => 'Kajin M̧ajeļ',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'palauan',
                'name' => 'Palauan',
                'native_name' => 'Tekoi er a Belau',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'carolinian',
                'name' => 'Carolinian',
                'native_name' => 'Refaluwasch',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'chamorro',
                'name' => 'Chamorro',
                'native_name' => 'Fino\' Chamoru',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ulithian',
                'name' => 'Ulithian',
                'native_name' => 'Woleaian',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('languages')->insert($languages);
    }
}
