<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_types', function (Blueprint $table) {
            $table->increments('ReportType_ID'); // Changed to auto-increment
            $table->string('Report_Name', 255);
            $table->text('Description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_types');
    }
};