<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->increments('Service_ID'); // Changed to auto-increment
            $table->string('Service_Name', 255);
            $table->text('Description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};