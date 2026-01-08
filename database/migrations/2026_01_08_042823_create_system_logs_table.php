<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->integer('Log_ID')->primary()->unsigned();
            $table->integer('User_ID')->unsigned();
            $table->string('Action', 255);
            $table->dateTime('Timestamp');
            $table->text('Description')->nullable();
            $table->timestamps();

            $table->foreign('User_ID')->references('User_ID')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
