<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('processing_statuses', function (Blueprint $table) {
            $table->increments('Processing_Status_ID'); // Fixed: was using wrong column name
            $table->string('Status_Name', 255); // Fixed: was using wrong column name
            $table->text('Description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_statuses');
    }
};