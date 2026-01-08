<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->integer('Appointment_ID')->primary()->unsigned();
            $table->integer('User_ID')->unsigned();
            $table->integer('Pet_ID')->unsigned();
            $table->integer('Service_ID')->unsigned();
            $table->string('Location', 255);
            $table->date('Date');
            $table->time('Time');
            $table->string('Status', 255);
            $table->text('Special_Notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('User_ID')->references('User_ID')->on('users');
            $table->foreign('Pet_ID')->references('Pet_ID')->on('pets');
            $table->foreign('Service_ID')->references('Service_ID')->on('service_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};