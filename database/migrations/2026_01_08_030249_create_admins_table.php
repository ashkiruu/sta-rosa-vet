<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->integer('User_ID')->primary()->unsigned(); // same as User_ID
            $table->foreign('User_ID')->references('User_ID')->on('users');

            $table->timestamps(); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};

