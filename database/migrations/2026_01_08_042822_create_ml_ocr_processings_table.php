<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ml_ocr_processing', function (Blueprint $table) {
            $table->increments('OCR_ID'); // Changed to auto-increment
            $table->integer('User_ID')->unsigned();
            $table->integer('CertificateType_ID')->unsigned();
            $table->string('Document_Image_Path', 255);
            $table->json('Extracted_Text')->nullable();
            $table->json('Parsed_Data')->nullable();
            $table->decimal('Confidence_Score', 5, 2)->nullable();
            $table->string('Address_Match_Status', 255)->nullable();
            $table->dateTime('Created_Date');
            $table->timestamps();

            $table->foreign('User_ID')->references('User_ID')->on('users');
            $table->foreign('CertificateType_ID')->references('CertificateType_ID')->on('certificate_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_ocr_processing');
    }
};