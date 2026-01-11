<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->increments('Certificate_ID'); // Changed to auto-increment
            $table->integer('Appointment_ID')->unsigned();
            $table->integer('Pet_ID')->unsigned();
            $table->integer('Owner_ID')->unsigned();
            $table->integer('CertificateType_ID')->unsigned();
            $table->string('Certificate_Number', 255);
            $table->string('Vet_Name', 255);
            $table->string('Signature', 255)->nullable();
            $table->date('Issue_Date');
            $table->string('File_Path', 255)->nullable();
            $table->timestamps();

            $table->foreign('Appointment_ID')->references('Appointment_ID')->on('appointments');
            $table->foreign('Pet_ID')->references('Pet_ID')->on('pets');
            $table->foreign('Owner_ID')->references('User_ID')->on('users');
            $table->foreign('CertificateType_ID')->references('CertificateType_ID')->on('certificate_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};