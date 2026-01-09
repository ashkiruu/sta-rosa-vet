<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * All 18 barangays of Sta. Rosa City, Laguna
     */
    public function run(): void
    {
        $barangays = [
            ['Barangay_ID' => 1, 'Barangay_Name' => 'Aplaya'],
            ['Barangay_ID' => 2, 'Barangay_Name' => 'Balibago'],
            ['Barangay_ID' => 3, 'Barangay_Name' => 'Caingin'],
            ['Barangay_ID' => 4, 'Barangay_Name' => 'Dila'],
            ['Barangay_ID' => 5, 'Barangay_Name' => 'Dita'],
            ['Barangay_ID' => 6, 'Barangay_Name' => 'Don Jose'],
            ['Barangay_ID' => 7, 'Barangay_Name' => 'Ibaba'],
            ['Barangay_ID' => 8, 'Barangay_Name' => 'Kanluran'],
            ['Barangay_ID' => 9, 'Barangay_Name' => 'Labas'],
            ['Barangay_ID' => 10, 'Barangay_Name' => 'Macabling'],
            ['Barangay_ID' => 11, 'Barangay_Name' => 'Malitlit'],
            ['Barangay_ID' => 12, 'Barangay_Name' => 'Malusak'],
            ['Barangay_ID' => 13, 'Barangay_Name' => 'Market Area'],
            ['Barangay_ID' => 14, 'Barangay_Name' => 'Pook'],
            ['Barangay_ID' => 15, 'Barangay_Name' => 'Pulong Santa Cruz'],
            ['Barangay_ID' => 16, 'Barangay_Name' => 'Santo Domingo'],
            ['Barangay_ID' => 17, 'Barangay_Name' => 'Sinalhan'],
            ['Barangay_ID' => 18, 'Barangay_Name' => 'Tagapo'],
        ];

        foreach ($barangays as $barangay) {
            DB::table('barangays')->insertOrIgnore([
                'Barangay_ID' => $barangay['Barangay_ID'],
                'Barangay_Name' => $barangay['Barangay_Name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "All 18 Sta. Rosa City barangays seeded successfully!\n";
    }
}