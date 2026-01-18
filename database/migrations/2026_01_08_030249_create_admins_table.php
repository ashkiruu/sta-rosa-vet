<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->integer('User_ID')->primary()->unsigned();
            $table->boolean('is_super_admin')->default(false);
            $table->string('admin_role', 50)->default('staff');
            $table->integer('created_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('User_ID')->references('User_ID')->on('users');
            $table->foreign('created_by')->references('User_ID')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};