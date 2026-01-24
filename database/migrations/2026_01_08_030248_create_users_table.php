<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('User_ID'); // custom primary key
            $table->integer('Barangay_ID')->unsigned();
            $table->integer('Verification_Status_ID')->unsigned();
            $table->integer('Account_Status_ID')->unsigned();
            $table->string('Username', 255);
            $table->string('Password', 255);
            $table->string('First_Name', 255);
            $table->string('Middle_Name', 255)->nullable();
            $table->string('Last_Name', 255);
            $table->string('Contact_Number', 255);
            $table->string('Email', 255);
            $table->text('Address');
            $table->dateTime('Registration_Date');
            $table->rememberToken();

            // foreign keys
            $table->foreign('Barangay_ID')->references('Barangay_ID')->on('barangays');
            $table->foreign('Verification_Status_ID')->references('Verification_Status_ID')->on('verification_statuses');
            $table->foreign('Account_Status_ID')->references('Account_Status_ID')->on('account_statuses');

            $table->timestamps(); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

