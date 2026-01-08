<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verification_statuses', function (Blueprint $table) {
            $table->integer('Verification_Status_ID')->primary()->unsigned();
            $table->string('Verification_Status_Name', 255);
            $table->text('Description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_statuses');
    }
};
