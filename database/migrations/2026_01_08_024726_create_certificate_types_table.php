<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_types', function (Blueprint $table) {
            $table->integer('CertificateType_ID')->primary();
            $table->string('Certificate_Name', 255);
            $table->text('Description')->nullable();
            $table->timestamps(); // optional, adds created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_types');
    }
};
