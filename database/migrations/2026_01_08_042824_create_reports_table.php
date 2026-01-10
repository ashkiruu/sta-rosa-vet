<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('Report_ID'); // Changed to auto-increment
            $table->integer('Certificate_ID')->unsigned();
            $table->integer('Pet_ID')->unsigned();
            $table->integer('User_ID')->unsigned();
            $table->integer('CertificateType_ID')->unsigned();
            $table->integer('ReportType_ID')->unsigned();
            $table->dateTime('Generation_Date');
            $table->string('Type', 255);
            $table->string('File_Path', 255)->nullable();
            $table->timestamps();

            $table->foreign('Certificate_ID')->references('Certificate_ID')->on('certificates');
            $table->foreign('Pet_ID')->references('Pet_ID')->on('pets');
            $table->foreign('User_ID')->references('User_ID')->on('users');
            $table->foreign('CertificateType_ID')->references('CertificateType_ID')->on('certificate_types');
            $table->foreign('ReportType_ID')->references('ReportType_ID')->on('report_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};