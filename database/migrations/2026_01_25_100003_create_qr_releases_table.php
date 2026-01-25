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
        Schema::create('qr_releases', function (Blueprint $table) {
            $table->id('QRRelease_ID');
            $table->unsignedBigInteger('Appointment_ID');
            $table->unsignedBigInteger('User_ID'); // Pet owner who receives the QR
            $table->string('Pet_Name');
            $table->string('Service')->nullable();
            $table->date('Scheduled_Date');
            $table->time('Scheduled_Time');
            $table->boolean('Released')->default(false);
            $table->datetime('Released_At')->nullable();
            $table->string('Released_By')->nullable();
            $table->string('QR_Path')->nullable(); // GCS path or local path
            $table->boolean('Seen')->default(false);
            $table->datetime('Seen_At')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique('Appointment_ID', 'unique_qr_appointment');
            $table->index('User_ID');
            $table->index('Released');
            $table->index(['User_ID', 'Released', 'Seen']); // For notification queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_releases');
    }
};
