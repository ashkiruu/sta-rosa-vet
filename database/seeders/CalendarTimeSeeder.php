<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates slots from 08:00 AM to 11:00 AM with 10-minute intervals.
     */
    public function run(): void
    {
        $startTime = Carbon::createFromTime(8, 0, 0);
        $endTime = Carbon::createFromTime(11, 0, 0);
        $count = 0;

        while ($startTime <= $endTime) {
            DB::table('calendar_time')->insertOrIgnore([
                'Slot_Val'     => $startTime->format('H:i'),     // 24-hour format: 08:10
                'Slot_Display' => $startTime->format('h:i A'),  // 12-hour format: 08:10 AM
                'Is_Active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Increment time by 10 minutes
            $startTime->addMinutes(10);
            $count++;
        }

        echo "Successfully seeded {$count} time slots (10-minute intervals)\n";
    }
}