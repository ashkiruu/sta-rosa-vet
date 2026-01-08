<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->integer('Barangay_ID')->primary(); // custom primary key
            $table->string('Barangay_Name', 255);
            $table->timestamps(); // optional, adds created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};