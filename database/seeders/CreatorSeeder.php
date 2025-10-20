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
                'name' => 'Dr. Maria Sakuma',
                'biography' => 'Educational researcher and author specializing in Pacific Island mathematics education.',
                'birth_year' => 1965,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'John Haglelgam',
                'biography' => 'Former President of FSM and educator, author of several books on Micronesian history.',
                'birth_year' => 1949,
                'death_year' => null,
                'nationality' => 'Micronesian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Emelihter Kihleng',
                'biography' => 'Pohnpeian poet and educator known for works on indigenous language preservation.',
                'birth_year' => 1971,
                'death_year' => null,
                'nationality' => 'Pohnpeian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rufino Mauricio',
                'biography' => 'Carolinian historian and storyteller, documenting traditional oral histories.',
                'birth_year' => 1958,
                'death_year' => null,
                'nationality' => 'Carolinian',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Illustrators
            [
                'name' => 'Lino Olopai',
                'biography' => 'Renowned Yapese artist and illustrator specializing in traditional cultural depictions.',
                'birth_year' => 1972,
                'death_year' => null,
                'nationality' => 'Yapese',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Masako Taimanglo',
                'biography' => 'Chamorro illustrator focusing on children\'s educational materials.',
                'birth_year' => 1980,
                'death_year' => null,
                'nationality' => 'Chamorro',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Joseph Urusemal',
                'biography' => 'Chuukese artist and former FSM President, illustrator of cultural and historical works.',
                'birth_year' => 1952,
                'death_year' => null,
                'nationality' => 'Chuukese',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Editors
            [
                'name' => 'Dr. Patricia Fifita',
                'biography' => 'Pacific education specialist and editor, University of Guam.',
                'birth_year' => 1968,
                'death_year' => null,
                'nationality' => 'Tongan-American',
                'website' => 'https://uog.edu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Robert Barcinas',
                'biography' => 'Senior editor specializing in Pacific Island educational publishing.',
                'birth_year' => 1975,
                'death_year' => null,
                'nationality' => 'Chamorro',
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dr. Dirk HR Spennemann',
                'biography' => 'Archaeologist and Pacific historian, editor of numerous cultural heritage publications.',
                'birth_year' => 1958,
                'death_year' => null,
                'nationality' => 'German-Australian',
                'website' => 'https://csu.edu.au',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('creators')->insert($creators);
    }
}
