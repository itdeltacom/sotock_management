<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('carte_grise_number');
            $table->date('carte_grise_expiry_date')->nullable();
            $table->string('assurance_number');
            $table->string('assurance_company');
            $table->date('assurance_expiry_date');
            $table->date('visite_technique_date')->nullable();
            $table->date('visite_technique_expiry_date');
            $table->date('vignette_expiry_date')->nullable();
            $table->string('file_carte_grise')->nullable();
            $table->string('file_assurance')->nullable();
            $table->string('file_visite_technique')->nullable();
            $table->string('file_vignette')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_documents');
    }
};