<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ml_ocr_processing', function (Blueprint $table) {
            $table->bigIncrements('OCR_ID'); 
            
            // Use unsignedInteger if your users table uses increments()
            // Or keep unsignedBigInteger if your users table uses id()
            // To be safe and compatible with most custom schemas, we use unsignedInteger here:
            $table->unsignedInteger('User_ID'); 
            $table->unsignedBigInteger('CertificateType_ID');
            
            $table->string('Document_Image_Path', 255);
            $table->json('Extracted_Text')->nullable();
            $table->json('Parsed_Data')->nullable();
            $table->decimal('Confidence_Score', 5, 2)->nullable();
            $table->string('Address_Match_Status', 255)->nullable();
            
            $table->dateTime('Created_Date')->useCurrent(); 
            $table->timestamps();

            // Standardized Foreign Key syntax
            $table->foreign('User_ID')
                ->references('User_ID')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('CertificateType_ID')
                ->references('CertificateType_ID')
                ->on('certificate_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_ocr_processing');
    }
};