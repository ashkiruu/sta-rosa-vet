<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificateTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'CertificateType_ID' => 1,
                'Certificate_Name' => 'National ID',
                'Description' => 'Philippine Identification System (PhilSys) Card',
            ],
            [
                'CertificateType_ID' => 2,
                'Certificate_Name' => 'Voters ID',
                'Description' => 'COMELEC Voter Identification Card',
            ],
            [
                'CertificateType_ID' => 3,
                'Certificate_Name' => 'Drivers License',
                'Description' => 'LTO Driver License Card',
            ],
        ];

        foreach ($types as $type) {
            DB::table('certificate_types')->updateOrInsert(
                ['CertificateType_ID' => $type['CertificateType_ID']],
                [
                    'Certificate_Name' => $type['Certificate_Name'],
                    'Description' => $type['Description'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        echo "Certificate types seeded successfully!\n";
    }
}