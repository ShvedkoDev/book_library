<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            [
                'name' => 'Pacific Education Press',
                'program_name' => 'Pacific Islands Educational Initiative',
                'address' => 'University of Hawaii\nHonolulu, HI 96822\nUSA',
                'website' => 'https://pacific-education.edu',
                'contact_email' => 'info@pacific-education.edu',
                'established_year' => 1995,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Micronesian Educational Resources',
                'program_name' => 'FSM Language Preservation Project',
                'address' => 'P.O. Box 1250\nPohnpei, FM 96941\nFederated States of Micronesia',
                'website' => 'https://mer.fm',
                'contact_email' => 'resources@mer.fm',
                'established_year' => 2001,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Island Academic Publishing',
                'program_name' => null,
                'address' => 'P.O. Box 234\nMajuro, MH 96960\nRepublic of the Marshall Islands',
                'website' => null,
                'contact_email' => 'academic@islandpub.mh',
                'established_year' => 1998,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cultural Heritage Books',
                'program_name' => 'Palau Cultural Documentation Series',
                'address' => 'P.O. Box 7000\nKoror, PW 96940\nRepublic of Palau',
                'website' => 'https://culturalheritage.pw',
                'contact_email' => 'heritage@culturalheritage.pw',
                'established_year' => 1999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CNMI Educational Foundation',
                'program_name' => 'Northern Marianas Textbook Program',
                'address' => 'P.O. Box 501234\nSaipan, MP 96950\nNorthern Mariana Islands',
                'website' => 'https://cnmi-edu.org',
                'contact_email' => 'foundation@cnmi-edu.org',
                'established_year' => 1986,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Traditional Knowledge Press',
                'program_name' => 'Yap Traditional Knowledge Project',
                'address' => 'Traditional Knowledge Center\nColonia, Yap 96943\nFederated States of Micronesia',
                'website' => null,
                'contact_email' => 'knowledge@yapgov.ym',
                'established_year' => 2003,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'University of Guam Press',
                'program_name' => 'UOG Academic Publishing',
                'address' => 'University of Guam\nMangilao, GU 96923\nGuam',
                'website' => 'https://uogpress.uog.edu',
                'contact_email' => 'press@uog.edu',
                'established_year' => 1972,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Oceanic Educational Materials',
                'program_name' => 'Pacific Regional Education Program',
                'address' => '1234 Education Way\nSuva, Fiji',
                'website' => 'https://oceanic-edu.fj',
                'contact_email' => 'materials@oceanic-edu.fj',
                'established_year' => 1989,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'FSM Department of Education',
                'program_name' => 'National Curriculum Development',
                'address' => 'P.O. Box PS-87\nPalikir, Pohnpei FM 96941\nFederated States of Micronesia',
                'website' => 'https://www.fsmgov.org/doe',
                'contact_email' => 'education@fsmgov.org',
                'established_year' => 1986,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pacific Scholars Collective',
                'program_name' => 'Indigenous Knowledge Research Initiative',
                'address' => '456 Research Boulevard\nAuckland, New Zealand',
                'website' => 'https://pacificscholars.org.nz',
                'contact_email' => 'collective@pacificscholars.org.nz',
                'established_year' => 2005,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('publishers')->insert($publishers);
    }
}
