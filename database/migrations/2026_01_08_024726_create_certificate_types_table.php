<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CREATE THE TABLE FIRST
        Schema::create('certificate_types', function (Blueprint $table) {
            $table->id('CertificateType_ID'); // Matches your Seeder
            $table->string('Certificate_Name');
            $table->text('Description')->nullable();
            $table->timestamps();
        });

        // 2. NOW ADD THE DATA
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
        // Be careful: Your previous down method was dropping 'admins'
        // Change it to drop the table this migration created
        Schema::dropIfExists('certificate_types');
    }
};