<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->integer('Pet_ID')->primary()->unsigned();
            $table->integer('Owner_ID')->unsigned();
            $table->integer('Species_ID')->unsigned();
            $table->string('Pet_Name', 255);
            $table->string('Breed', 255)->nullable();
            $table->string('Sex', 255);
            $table->date('Date_of_Birth');
            $table->integer('Age');
            $table->string('Color', 255);
            $table->string('Reproductive_Status', 255);
            $table->dateTime('Registration_Date');

            // foreign keys
            $table->foreign('Owner_ID')->references('User_ID')->on('users');
            $table->foreign('Species_ID')->references('Species_ID')->on('species');

            $table->timestamps(); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};

