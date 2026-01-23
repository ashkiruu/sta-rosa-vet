<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration modifies the reports table to support weekly aggregate reports
     * instead of per-certificate reports.
     */
    public function up(): void
    {
        // First, drop the existing reports table if it exists
        Schema::dropIfExists('reports');

        // Create the new reports table structure for weekly reports
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('Report_ID');
            $table->string('Report_Number', 100)->unique();
            $table->integer('ReportType_ID')->unsigned();
            $table->integer('Week_Number');
            $table->integer('Year');
            $table->date('Start_Date');
            $table->date('End_Date');
            $table->string('Generated_By', 255);
            $table->dateTime('Generated_At');
            $table->string('File_Path', 255)->nullable();
            $table->integer('Record_Count')->default(0);
            $table->json('Summary')->nullable(); // Store summary statistics as JSON
            $table->timestamps();

            $table->foreign('ReportType_ID')
                  ->references('ReportType_ID')
                  ->on('report_types')
                  ->onDelete('cascade');

            // Index for faster lookups
            $table->index(['Week_Number', 'Year']);
            $table->index(['ReportType_ID', 'Year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');

        // Recreate original structure if needed (optional - for rollback)
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('Report_ID');
            $table->integer('Certificate_ID')->unsigned();
            $table->integer('Pet_ID')->unsigned();
            $table->integer('User_ID')->unsigned();
            $table->integer('CertificateType_ID')->unsigned();
            $table->integer('ReportType_ID')->unsigned();
            $table->dateTime('Generation_Date');
            $table->string('Type', 255);
            $table->string('File_Path', 255)->nullable();
            $table->timestamps();
        });
    }
};