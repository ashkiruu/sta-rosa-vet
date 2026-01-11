<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
    [
        'Service_ID' => 1,
        'Service_Name' => 'Vaccination',
        'Description' => 'Pet vaccination services',
    ],
    [
        'Service_ID' => 2,
        'Service_Name' => 'Consultation',
        'Description' => 'General health consultation',
    ],
    [
        'Service_ID' => 3,
        'Service_Name' => 'Treatment',
        'Description' => 'Medical treatment services',
    ],
];

        foreach ($services as $service) {
            DB::table('service_types')->insertOrIgnore([
                'Service_ID' => $service['Service_ID'],
                'Service_Name' => $service['Service_Name'],
                'Description' => $service['Description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Service types seeded successfully!\n";
    }
}