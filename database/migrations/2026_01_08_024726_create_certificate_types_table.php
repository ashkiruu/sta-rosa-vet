<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates certificate_types to match service_types
     */
    public function up(): void
    {
        // Clear existing certificate types and add service-matching ones
        DB::table('certificate_types')->truncate();
        
        $types = [
            [
                'CertificateType_ID' => 1,
                'Certificate_Name' => 'Vaccination Certificate',
                'Description' => 'Certificate for pet vaccination services including anti-rabies and other preventive vaccines',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CertificateType_ID' => 2,
                'Certificate_Name' => 'Deworming Certificate',
                'Description' => 'Certificate for deworming treatment services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CertificateType_ID' => 3,
                'Certificate_Name' => 'Checkup Certificate',
                'Description' => 'Certificate for general health checkup and follow-up checkup services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('certificate_types')->insert($types);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original certificate types (ID documents)
        DB::table('certificate_types')->truncate();
        
        $types = [
            [
                'CertificateType_ID' => 1,
                'Certificate_Name' => 'National ID',
                'Description' => 'Philippine Identification System (PhilSys) Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CertificateType_ID' => 2,
                'Certificate_Name' => 'Voters ID',
                'Description' => 'COMELEC Voter Identification Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CertificateType_ID' => 3,
                'Certificate_Name' => 'Drivers License',
                'Description' => 'LTO Driver License Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('certificate_types')->insert($types);
    }
};