<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendar_time', function (Blueprint $table) {
            $table->increments('Slot_ID'); 
            $table->string('Slot_Val', 10)->unique(); 
            $table->string('Slot_Display', 20); 
            $table->boolean('Is_Active')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // FIX: Change this to match the table created in the up() method
        Schema::dropIfExists('calendar_time');
    }
};