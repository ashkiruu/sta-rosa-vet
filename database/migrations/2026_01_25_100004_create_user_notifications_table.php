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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id('Notification_ID');
            $table->unsignedBigInteger('User_ID');
            $table->string('Type'); // 'appointment_approved', 'appointment_declined', 'qr_ready', etc.
            $table->string('Title');
            $table->text('Message');
            $table->unsignedBigInteger('Reference_ID')->nullable(); // Appointment_ID or other reference
            $table->string('Reference_Type')->nullable(); // 'appointment', 'certificate', etc.
            $table->json('Data')->nullable(); // Additional data as JSON
            $table->boolean('Seen')->default(false);
            $table->datetime('Seen_At')->nullable();
            $table->datetime('Expires_At')->nullable(); // Auto-cleanup old notifications
            $table->timestamps();
            
            // Indexes
            $table->index('User_ID');
            $table->index('Type');
            $table->index('Seen');
            $table->index(['User_ID', 'Seen']); // For "unseen notifications" query
            $table->index('Expires_At'); // For cleanup job
            $table->index(['Reference_ID', 'Reference_Type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
