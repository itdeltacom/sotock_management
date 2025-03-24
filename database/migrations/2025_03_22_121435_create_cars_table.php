<?php

use App\Models\FuelType;
use App\Models\TransmissionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->integer('seats')->default(5);
            $table->string('transmission')->default('automatic');
            $table->string('fuel_type')->default('petrol');
            $table->integer('mileage')->nullable();
            $table->string('engine_capacity')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->string('main_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};