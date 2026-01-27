<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds Civil_Status, Years_Of_Residency, and Birthdate fields to users table
     * for certificate generation purposes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('Civil_Status', 50)->nullable()->after('Address');
            $table->string('Years_Of_Residency', 100)->nullable()->after('Civil_Status');
            $table->date('Birthdate')->nullable()->after('Years_Of_Residency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['Civil_Status', 'Years_Of_Residency', 'Birthdate']);
        });
    }
};