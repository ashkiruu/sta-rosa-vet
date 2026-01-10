<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            ['Species_ID' => 1, 'Species_Name' => 'Dog', 'Description' => 'Canine'],
            ['Species_ID' => 2, 'Species_Name' => 'Cat', 'Description' => 'Feline'],
            ['Species_ID' => 3, 'Species_Name' => 'Bird', 'Description' => 'Avian'],
            ['Species_ID' => 4, 'Species_Name' => 'Other', 'Description' => 'Other species'],
        ];

        foreach ($species as $s) {
            DB::table('species')->insertOrIgnore([
                'Species_ID' => $s['Species_ID'],
                'Species_Name' => $s['Species_Name'],
                'Description' => $s['Description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Species seeded successfully!\n";
    }
}