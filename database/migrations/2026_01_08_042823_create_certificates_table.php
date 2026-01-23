<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration modifies the certificates table to store all certificate data
     * that was previously stored in JSON file.
     */
    public function up(): void
    {
        Schema::dropIfExists('certificates');

        Schema::create('certificates', function (Blueprint $table) {
            $table->increments('Certificate_ID');
            $table->string('Certificate_Number', 100)->unique();
            $table->integer('Appointment_ID')->unsigned()->nullable();
            $table->integer('Pet_ID')->unsigned()->nullable();
            $table->integer('Owner_ID')->unsigned()->nullable();
            $table->integer('CertificateType_ID')->unsigned();
            
            // Service Information
            $table->string('Service_Type', 255);
            $table->string('Service_Category', 50); // vaccination, deworming, checkup, other
            
            // Pet Information (stored in certificate for historical record)
            $table->string('Pet_Name', 255);
            $table->string('Animal_Type', 100);
            $table->string('Pet_Gender', 50);
            $table->string('Pet_Age', 100);
            $table->string('Pet_Breed', 255);
            $table->string('Pet_Color', 100);
            $table->date('Pet_DOB')->nullable();
            
            // Owner Information (stored in certificate for historical record)
            $table->string('Owner_Name', 255);
            $table->text('Owner_Address');
            $table->string('Owner_Phone', 50);
            $table->string('Civil_Status', 50)->nullable();
            $table->string('Years_Of_Residency', 100)->nullable();
            $table->date('Owner_Birthdate')->nullable();
            
            // Service Dates
            $table->date('Service_Date')->nullable();
            $table->date('Next_Service_Date')->nullable();
            
            // Vaccination-specific fields
            $table->string('Vaccine_Type', 100)->nullable(); // anti-rabies, other
            $table->string('Vaccine_Used', 255)->nullable();
            $table->string('Lot_Number', 100)->nullable();
            
            // Deworming-specific fields
            $table->string('Medicine_Used', 255)->nullable();
            $table->string('Dosage', 100)->nullable();
            
            // Checkup-specific fields
            $table->text('Findings')->nullable();
            $table->text('Recommendations')->nullable();
            
            // Veterinarian Details
            $table->string('Vet_Name', 255);
            $table->string('License_Number', 100);
            $table->string('PTR_Number', 100);
            
            // System Fields
            $table->enum('Status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->string('File_Path', 255)->nullable();
            $table->string('Created_By', 255)->nullable();
            $table->string('Approved_By', 255)->nullable();
            $table->timestamp('Approved_At')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('Appointment_ID')->references('Appointment_ID')->on('appointments')->onDelete('set null');
            $table->foreign('Pet_ID')->references('Pet_ID')->on('pets')->onDelete('set null');
            $table->foreign('Owner_ID')->references('User_ID')->on('users')->onDelete('set null');
            $table->foreign('CertificateType_ID')->references('CertificateType_ID')->on('certificate_types');

            // Indexes
            $table->index(['Status']);
            $table->index(['Service_Category']);
            $table->index(['Service_Date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');

        // Recreate original simple structure
        Schema::create('certificates', function (Blueprint $table) {
            $table->increments('Certificate_ID');
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
};