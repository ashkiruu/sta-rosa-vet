<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinic_schedules', function (Blueprint $table) {
            $table->id('Schedule_ID');
            $table->date('date')->nullable(); // Null for default closed days
            $table->enum('type', ['default_closed', 'opened', 'closed']);
            $table->tinyInteger('day_of_week')->nullable(); // 0=Sunday, 6=Saturday
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique('date', 'unique_schedule_date');
            $table->index('type');
            $table->index('day_of_week');
        });
        
        // Insert default closed days (Saturday=6, Sunday=0)
        DB::table('clinic_schedules')->insert([
            ['date' => null, 'type' => 'default_closed', 'day_of_week' => 0, 'notes' => 'Sunday - Default closed', 'created_at' => now(), 'updated_at' => now()],
            ['date' => null, 'type' => 'default_closed', 'day_of_week' => 6, 'notes' => 'Saturday - Default closed', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_schedules');
    }
};
