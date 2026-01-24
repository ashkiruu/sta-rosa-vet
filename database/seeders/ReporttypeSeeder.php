<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reporttype;

class ReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reportTypes = [
            [
                'ReportType_ID' => 1,
                'Report_Name' => 'Anti-Rabies Vaccination Report',
                'Description' => 'Weekly report for anti-rabies vaccination services including client information, pet details, and vaccination records.',
            ],
            [
                'ReportType_ID' => 2,
                'Report_Name' => 'Routine Services Report',
                'Description' => 'Weekly report for all routine veterinary services including checkups, deworming, and other services.',
            ],
        ];

        foreach ($reportTypes as $type) {
            ReportType::updateOrCreate(
                ['ReportType_ID' => $type['ReportType_ID']],
                $type
            );
        }
    }
}
//