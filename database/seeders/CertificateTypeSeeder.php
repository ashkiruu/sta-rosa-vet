<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificateTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Certificate types now match service types:
     * 1 = Vaccination Certificate (matches Service_ID 1: Vaccination)
     * 2 = Deworming Certificate (matches Service_ID 2: Deworming)
     * 3 = Checkup Certificate (matches Service_ID 3: Checkup/FF-Checkup)
     */
    public function run(): void
    {
        $types = [
            [
                'CertificateType_ID' => 1,
                'Certificate_Name' => 'Vaccination Certificate',
                'Description' => 'Certificate for pet vaccination services including anti-rabies and other preventive vaccines',
            ],
            [
                'CertificateType_ID' => 2,
                'Certificate_Name' => 'Deworming Certificate',
                'Description' => 'Certificate for deworming treatment services',
            ],
            [
                'CertificateType_ID' => 3,
                'Certificate_Name' => 'Checkup Certificate',
                'Description' => 'Certificate for general health checkup and follow-up checkup services',
            ],
        ];

        foreach ($types as $type) {
            DB::table('certificate_types')->updateOrInsert(
                ['CertificateType_ID' => $type['CertificateType_ID']],
                [
                    'Certificate_Name' => $type['Certificate_Name'],
                    'Description' => $type['Description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        echo "Certificate types seeded successfully (matching service types)!\n";
        echo "1 = Vaccination Certificate\n";
        echo "2 = Deworming Certificate\n";
        echo "3 = Checkup Certificate\n";
    }
}