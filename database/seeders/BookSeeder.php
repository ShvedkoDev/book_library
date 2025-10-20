<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert books
        $this->seedBooks();
        $this->seedBookLanguages();
        $this->seedBookCreators();
        $this->seedBookLocations();
        $this->seedBookFiles();
        $this->seedLibraryReferences();
        $this->seedBookClassifications();
        $this->seedBookRelationships();
    }

    private function seedBooks()
    {
        $books = [
            [
                'id' => 1,
                'internal_id' => 'P001-a',
                'palm_code' => 'TAW14',
                'title' => 'A?a?n a?tin mwa?a?n we pikinik',
                'subtitle' => '(trial version)',
                'translated_title' => '[Boys at the picnic]',
                'physical_type' => 'book',
                'collection_id' => 1, // PALM trial
                'publisher_id' => 1, // UH SSRI
                'publication_year' => 1979,
                'pages' => 8,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'full',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'internal_id' => 'P006-a',
                'palm_code' => 'KNT1',
                'title' => 'Ati ac Kenye oasr ke kuhlahs se',
                'subtitle' => '(trial version)',
                'translated_title' => '[Twin boys go to first grade]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1983,
                'pages' => 40,
                'description' => null,
                'toc' => null,
                'notes_issue' => 'This title is not included on the PALM CD-ROM (1999).',
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'full',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'internal_id' => 'P008-a',
                'palm_code' => 'unavailable',
                'title' => 'Ahkfuhlwactyen sun lwen ohnkohsr',
                'subtitle' => '(trial version)',
                'translated_title' => '[Celebrating June 6th]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1979,
                'pages' => null,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'unavailable',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'internal_id' => 'P015-a',
                'palm_code' => 'unavailable',
                'title' => 'Bitiiru sukuul ngea seenseey rooraed',
                'subtitle' => '(trial version)',
                'translated_title' => '[Students and their teacher]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1978,
                'pages' => null,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'unavailable',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'internal_id' => 'P019-a',
                'palm_code' => 'unavailable',
                'title' => 'Boechii tiir ni ba balyaang',
                'subtitle' => '(trial version)',
                'translated_title' => '[A naughty kid]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1979,
                'pages' => null,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'unavailable',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'internal_id' => 'P023-a',
                'palm_code' => 'TCC4',
                'title' => 'Chiechiach kewe',
                'subtitle' => '(trial version)',
                'translated_title' => '[Our friends]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1980,
                'pages' => 5,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'full',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'internal_id' => 'P025-a',
                'palm_code' => 'PoOJ22',
                'title' => 'Dahk emen oh karahs emen weir',
                'subtitle' => '(trial version)',
                'translated_title' => '[Race between a crab and a needlefish]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1979,
                'pages' => 8,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'full',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'internal_id' => 'P026-a',
                'palm_code' => 'PoOJ25',
                'title' => 'Dahme e kin uhd kang',
                'subtitle' => '(trial version)',
                'translated_title' => '[What eats what?]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1980,
                'pages' => 16,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'full',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'internal_id' => 'P029-a',
                'palm_code' => 'unavailable',
                'title' => 'Duen laid ki uhk',
                'subtitle' => '(trial version)',
                'translated_title' => '[Net fishing]',
                'physical_type' => 'book',
                'collection_id' => 1,
                'publisher_id' => 1,
                'publication_year' => 1978,
                'pages' => null,
                'description' => null,
                'toc' => null,
                'notes_issue' => null,
                'notes_content' => null,
                'contact' => null,
                'access_level' => 'unavailable',
                'vla_standard' => null,
                'vla_benchmark' => null,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('books')->insert($books);
    }

    private function seedBookLanguages()
    {
        $bookLanguages = [
            // Book 1 - Chuukese
            ['book_id' => 1, 'language_id' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 2 - Kosraean
            ['book_id' => 2, 'language_id' => 2, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 3 - Kosraean
            ['book_id' => 3, 'language_id' => 2, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 4 - Yapese
            ['book_id' => 4, 'language_id' => 3, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 5 - Yapese
            ['book_id' => 5, 'language_id' => 3, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 6 - Chuukese
            ['book_id' => 6, 'language_id' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 7 - Pohnpeian
            ['book_id' => 7, 'language_id' => 4, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 8 - Pohnpeian
            ['book_id' => 8, 'language_id' => 4, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],

            // Book 9 - Pohnpeian
            ['book_id' => 9, 'language_id' => 4, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('book_languages')->insert($bookLanguages);
    }

    private function seedBookCreators()
    {
        $bookCreators = [
            // Book 1: William, Alvios (author) + Layne, Chris (illustrator)
            ['book_id' => 1, 'creator_id' => 1, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 1, 'creator_id' => 13, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 2: Talley, Noah (author) + Wren (illustrator)
            ['book_id' => 2, 'creator_id' => 2, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 2, 'creator_id' => 14, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 3: Timothy, Shiro (author) + assistants + Buccholz, Donald L. (illustrator)
            ['book_id' => 3, 'creator_id' => 3, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 3, 'creator_id' => 10, 'creator_type' => 'contributor', 'role_description' => 'assisted by', 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 3, 'creator_id' => 11, 'creator_type' => 'contributor', 'role_description' => 'assisted by', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 3, 'creator_id' => 15, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 4: Gilmar, Maelynn (author and illustrator)
            ['book_id' => 4, 'creator_id' => 4, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 4, 'creator_id' => 4, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 5: Tinngin (author and illustrator)
            ['book_id' => 5, 'creator_id' => 5, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 5, 'creator_id' => 5, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 6: Cheipot, Chirano (author) + Nakamura, Deby (illustrator)
            ['book_id' => 6, 'creator_id' => 6, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 6, 'creator_id' => 16, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 7: Uolai, Augustin (author) + Johnny, Oliver (translator) + Woo, Eric (illustrator)
            ['book_id' => 7, 'creator_id' => 7, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 7, 'creator_id' => 12, 'creator_type' => 'translator', 'role_description' => 'translated by', 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 7, 'creator_id' => 17, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 8: Sakuma, Miriam U. (author) + Johnny, Oliver (translator) + Fujioka, Calvin A. (illustrator)
            ['book_id' => 8, 'creator_id' => 8, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 8, 'creator_id' => 12, 'creator_type' => 'translator', 'role_description' => 'translated by', 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 8, 'creator_id' => 18, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],

            // Book 9: Santos, Ioannis (author) + Goodenow, Joy L. (illustrator)
            ['book_id' => 9, 'creator_id' => 9, 'creator_type' => 'author', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 9, 'creator_id' => 19, 'creator_type' => 'illustrator', 'role_description' => null, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('book_creators')->insert($bookCreators);
    }

    private function seedBookLocations()
    {
        $bookLocations = [
            // Book 1 - Chuuk Lagoon, Chuuk State
            ['book_id' => 1, 'location_id' => 5, 'created_at' => now(), 'updated_at' => now()], // Chuuk Lagoon
            ['book_id' => 1, 'location_id' => 1, 'created_at' => now(), 'updated_at' => now()], // Chuuk State

            // Book 2 - Kosrae, Kosrae State
            ['book_id' => 2, 'location_id' => 6, 'created_at' => now(), 'updated_at' => now()], // Kosrae
            ['book_id' => 2, 'location_id' => 2, 'created_at' => now(), 'updated_at' => now()], // Kosrae State

            // Book 3 - Kosrae, Kosrae State
            ['book_id' => 3, 'location_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 3, 'location_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // Book 4 - Yap, Yap State
            ['book_id' => 4, 'location_id' => 7, 'created_at' => now(), 'updated_at' => now()], // Yap
            ['book_id' => 4, 'location_id' => 3, 'created_at' => now(), 'updated_at' => now()], // Yap State

            // Book 5 - Yap, Yap State
            ['book_id' => 5, 'location_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 5, 'location_id' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Book 6 - Chuuk Lagoon, Chuuk State
            ['book_id' => 6, 'location_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 6, 'location_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Book 7 - Pohnpei, Pohnpei State
            ['book_id' => 7, 'location_id' => 8, 'created_at' => now(), 'updated_at' => now()], // Pohnpei
            ['book_id' => 7, 'location_id' => 4, 'created_at' => now(), 'updated_at' => now()], // Pohnpei State

            // Book 8 - Pohnpei, Pohnpei State
            ['book_id' => 8, 'location_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 8, 'location_id' => 4, 'created_at' => now(), 'updated_at' => now()],

            // Book 9 - Pohnpei, Pohnpei State
            ['book_id' => 9, 'location_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 9, 'location_id' => 4, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('book_locations')->insert($bookLocations);
    }

    private function seedBookFiles()
    {
        $bookFiles = [
            // Book 1 - Has PDF
            [
                'book_id' => 1,
                'file_type' => 'pdf',
                'file_path' => 'books/pdf/PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik.pdf',
                'filename' => 'PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik.pdf',
                'file_size' => null,
                'mime_type' => 'application/pdf',
                'is_primary' => true,
                'digital_source' => 'Downloaded from UH Scholar Space (https://hdl.handle.net/10125/42190) by iREi (2025).',
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => 1,
                'file_type' => 'thumbnail',
                'file_path' => 'books/thumbnails/PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik.png',
                'filename' => 'PALM - Printed [Trial version] - CHUUKESE - A?a?n a?tin mwa?a?n we Pikinik.png',
                'file_size' => null,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'digital_source' => null,
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 2 - Has PDF
            [
                'book_id' => 2,
                'file_type' => 'pdf',
                'file_path' => 'books/pdf/PALM - Printed [Trial version] - KOSRAEAN - Ati ac Kenye Oasr ke Kuhlahs Se KNT 1.pdf',
                'filename' => 'PALM - Printed [Trial version] - KOSRAEAN - Ati ac Kenye Oasr ke Kuhlahs Se KNT 1.pdf',
                'file_size' => null,
                'mime_type' => 'application/pdf',
                'is_primary' => true,
                'digital_source' => 'Downloaded from UH Scholar Space (https://hdl.handle.net/10125/42190) by iREi (2025).',
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => 2,
                'file_type' => 'thumbnail',
                'file_path' => 'books/thumbnails/PALM - Printed [Trial version] - KOSRAEAN - Ati ac Kenye Oasr ke Kuhlahs Se KNT 1.png',
                'filename' => 'PALM - Printed [Trial version] - KOSRAEAN - Ati ac Kenye Oasr ke Kuhlahs Se KNT 1.png',
                'file_size' => null,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'digital_source' => null,
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 6 - Has PDF
            [
                'book_id' => 6,
                'file_type' => 'pdf',
                'file_path' => 'books/pdf/PALM - Printed [Trial version] - CHUUKESE - Chiechiach Kewe TCC 4.pdf',
                'filename' => 'PALM - Printed [Trial version] - CHUUKESE - Chiechiach Kewe TCC 4.pdf',
                'file_size' => null,
                'mime_type' => 'application/pdf',
                'is_primary' => true,
                'digital_source' => 'Downloaded from UH Scholar Space (https://hdl.handle.net/10125/42190) by iREi (2025).',
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => 6,
                'file_type' => 'thumbnail',
                'file_path' => 'books/thumbnails/PALM - Printed [Trial version] - CHUUKESE - Chiechiach Kewe TCC 4.png',
                'filename' => 'PALM - Printed [Trial version] - CHUUKESE - Chiechiach Kewe TCC 4.png',
                'file_size' => null,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'digital_source' => null,
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 7 - Has PDF
            [
                'book_id' => 7,
                'file_type' => 'pdf',
                'file_path' => 'books/pdf/PALM - Printed [Trial version] - POHNPEIAN - Dahk Emen Oh Karahs Emen Weir - Level7.pdf',
                'filename' => 'PALM - Printed [Trial version] - POHNPEIAN - Dahk Emen Oh Karahs Emen Weir - Level7.pdf',
                'file_size' => null,
                'mime_type' => 'application/pdf',
                'is_primary' => true,
                'digital_source' => 'PDF created by iREi (2025) from a hard copy provided by the COM library, Pohnpei.',
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => 7,
                'file_type' => 'thumbnail',
                'file_path' => 'books/thumbnails/PALM - Printed [Trial version] - POHNPEIAN - Dahk Emen Oh Karahs Emen Weir - Level7.png',
                'filename' => 'PALM - Printed [Trial version] - POHNPEIAN - Dahk Emen Oh Karahs Emen Weir - Level7.png',
                'file_size' => null,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'digital_source' => null,
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 8 - Has PDF
            [
                'book_id' => 8,
                'file_type' => 'pdf',
                'file_path' => 'books/pdf/PALM - Printed [Trial version] - POHNPEIAN - Dahme e Kin Uhd Kang.pdf',
                'filename' => 'PALM - Printed [Trial version] - POHNPEIAN - Dahme e Kin Uhd Kang.pdf',
                'file_size' => null,
                'mime_type' => 'application/pdf',
                'is_primary' => true,
                'digital_source' => 'PDF created by iREi (2025) from a hard copy provided by the COM library, Pohnpei.',
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => 8,
                'file_type' => 'thumbnail',
                'file_path' => 'books/thumbnails/PALM - Printed [Trial version] - POHNPEIAN - Dahme e Kin Uhd Kang.png',
                'filename' => 'PALM - Printed [Trial version] - POHNPEIAN - Dahme e Kin Uhd Kang.png',
                'file_size' => null,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'digital_source' => null,
                'external_url' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('book_files')->insert($bookFiles);
    }

    private function seedLibraryReferences()
    {
        $libraryReferences = [
            // Book 3 - UH library reference
            [
                'book_id' => 3,
                'library_code' => 'UH',
                'library_name' => 'University of Hawaii Library',
                'reference_number' => null,
                'call_number' => 'Pac.PL6252.K86K67 1979',
                'catalog_link' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 4 - UH library reference
            [
                'book_id' => 4,
                'library_code' => 'UH',
                'library_name' => 'University of Hawaii Library',
                'reference_number' => null,
                'call_number' => 'Pac.PL6341.Z77F35 1978 v.2',
                'catalog_link' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 5 - UH library reference
            [
                'book_id' => 5,
                'library_code' => 'UH',
                'library_name' => 'University of Hawaii Library',
                'reference_number' => null,
                'call_number' => 'Pac.PL6341.Z77C45 1979',
                'catalog_link' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Book 9 - UH library reference
            [
                'book_id' => 9,
                'library_code' => 'UH',
                'library_name' => 'University of Hawaii Library',
                'reference_number' => null,
                'call_number' => 'Pac.PL6295.Z77S26 1978 v.2',
                'catalog_link' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('library_references')->insert($libraryReferences);
    }

    private function seedBookClassifications()
    {
        // All books are: Literacy Development > Readers > Instructional
        // Get the classification IDs (assuming they were seeded with ClassificationValueSeeder)
        // Genre: Readers (id 10 in ClassificationValueSeeder)
        // Type: Instructional (id 11)
        // Themes/Uses: Language Arts (id 16)

        $bookClassifications = [];

        for ($bookId = 1; $bookId <= 9; $bookId++) {
            $bookClassifications[] = [
                'book_id' => $bookId,
                'classification_value_id' => 10, // Readers
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $bookClassifications[] = [
                'book_id' => $bookId,
                'classification_value_id' => 16, // Language Arts
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('book_classifications')->insert($bookClassifications);
    }

    private function seedBookRelationships()
    {
        $bookRelationships = [
            // P001 related to P001
            ['book_id' => 1, 'related_book_id' => 1, 'relationship_type' => 'same_version', 'relationship_code' => 'P001', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P006 related to P006
            ['book_id' => 2, 'related_book_id' => 2, 'relationship_type' => 'same_version', 'relationship_code' => 'P006', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P008 related to P008
            ['book_id' => 3, 'related_book_id' => 3, 'relationship_type' => 'same_version', 'relationship_code' => 'P008', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P015 related to P015
            ['book_id' => 4, 'related_book_id' => 4, 'relationship_type' => 'same_version', 'relationship_code' => 'P015', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P019 related to P019
            ['book_id' => 5, 'related_book_id' => 5, 'relationship_type' => 'same_version', 'relationship_code' => 'P019', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P023 related to P023
            ['book_id' => 6, 'related_book_id' => 6, 'relationship_type' => 'same_version', 'relationship_code' => 'P023', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P025 related to P025
            ['book_id' => 7, 'related_book_id' => 7, 'relationship_type' => 'same_version', 'relationship_code' => 'P025', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P026 related to P026
            ['book_id' => 8, 'related_book_id' => 8, 'relationship_type' => 'same_version', 'relationship_code' => 'P026', 'description' => null, 'created_at' => now(), 'updated_at' => now()],

            // P029 related to P029
            ['book_id' => 9, 'related_book_id' => 9, 'relationship_type' => 'same_version', 'relationship_code' => 'P029', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('book_relationships')->insert($bookRelationships);
    }
}
