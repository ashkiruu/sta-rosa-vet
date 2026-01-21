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
                'Description' => 'Pet vaccination services including anti-rabies and other preventive vaccines',
            ],
            [
                'Service_ID' => 2,
                'Service_Name' => 'Deworming',
                'Description' => 'Deworming treatment for pets to eliminate internal parasites',
            ],
            [
                'Service_ID' => 3,
                'Service_Name' => 'Checkup/FF-Checkup',
                'Description' => 'General health checkup and follow-up checkup services',
            ],
        ];

        foreach ($services as $service) {
            DB::table('service_types')->updateOrInsert(
                ['Service_ID' => $service['Service_ID']],
                [
                    'Service_Name' => $service['Service_Name'],
                    'Description' => $service['Description'],
                    'updated_at' => now(),
                ]
            );
        }

        echo "Service types updated successfully!\n";
    }
}