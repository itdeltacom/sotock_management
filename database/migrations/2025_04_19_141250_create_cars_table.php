<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('model');
            $table->string('year');
            $table->string('chassis_number')->unique();
            $table->string('matricule')->unique();
            $table->string('color')->nullable();
            $table->date('mise_en_service_date');
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable'])->default('available');
            $table->decimal('daily_price', 10, 2);
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->enum('fuel_type', ['diesel', 'gasoline', 'electric', 'hybrid'])->default('diesel');
            $table->integer('mileage')->default(0);
            $table->enum('transmission', ['manual', 'automatic'])->default('manual');
            $table->integer('seats')->default(5);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};