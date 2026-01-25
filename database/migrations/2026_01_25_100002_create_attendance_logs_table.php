<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id('Attendance_ID');
            $table->unsignedBigInteger('Appointment_ID');
            $table->unsignedBigInteger('User_ID'); // Pet owner
            $table->string('Pet_Name');
            $table->string('Owner_Name');
            $table->string('Service')->nullable();
            $table->date('Scheduled_Date');
            $table->time('Scheduled_Time');
            $table->datetime('Check_In_Time');
            $table->date('Check_In_Date');
            $table->string('Scanned_By')->default('Receptionist');
            $table->enum('Status', ['checked_in', 'completed', 'no_show'])->default('checked_in');
            $table->timestamps();
            
            // Indexes
            $table->unique('Appointment_ID', 'unique_appointment_attendance');
            $table->index('User_ID');
            $table->index('Check_In_Date');
            $table->index('Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
