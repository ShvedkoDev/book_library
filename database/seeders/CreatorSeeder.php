<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creators = [
            // Authors
            [
                'name' => 'William, Alvios',
                'biography' => 'Author of Chuukese language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Talley, Noah',
                'biography' => 'Author of Kosraean language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Timothy, Shiro',
                'biography' => 'Author of Kosraean language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gilmar, Maelynn',
                'biography' => 'Author and illustrator of Yapese language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tinngin',
                'biography' => 'Author and illustrator of Yapese language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cheipot, Chirano',
                'biography' => 'Author of Chuukese language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Uolai, Augustin',
                'biography' => 'Author of Pohnpeian language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sakuma, Miriam U.',
                'biography' => 'Author of Pohnpeian language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Santos, Ioannis',
                'biography' => 'Author of Pohnpeian language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Translators and assistants
            [
                'name' => 'Shrew, Palikun',
                'biography' => 'Translation assistant for Kosraean language materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Skilling, Joab',
                'biography' => 'Translation assistant for Kosraean language materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Johnny, Oliver',
                'biography' => 'Translator of Pohnpeian language educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Illustrators
            [
                'name' => 'Layne, Chris',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wren',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Buccholz, Donald L.',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nakamura, Deby',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Woo, Eric',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fujioka, Calvin A.',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Goodenow, Joy L.',
                'biography' => 'Illustrator for PALM educational materials',
                'birth_year' => null,
                'death_year' => null,
                'nationality' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('creators')->insert($creators);
    }
}
