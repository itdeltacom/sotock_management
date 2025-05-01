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
            // Fields from both migrations
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand_name'); // From first migration
            $table->foreignId('brand_id')->constrained()->onDelete('cascade'); // From second migration
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // From second migration
            $table->string('model'); // From first migration
            $table->string('year'); // From first migration
            $table->string('chassis_number')->unique(); // From first migration
            $table->string('matricule')->unique(); // From first migration
            $table->string('color')->nullable(); // From first migration
            $table->date('mise_en_service_date'); // From first migration
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable'])->default('available'); // From first migration
            $table->boolean('is_available')->default(true); // From second migration
            
            // Pricing fields
            $table->decimal('daily_price', 10, 2); // From first migration (renamed from price_per_day)
            $table->decimal('price_per_day', 10, 2); // From second migration (alias for daily_price)
            $table->decimal('weekly_price', 10, 2)->nullable(); // From first migration
            $table->decimal('monthly_price', 10, 2)->nullable(); // From first migration
            $table->decimal('discount_percentage', 5, 2)->default(0); // From second migration
            
            // Technical specifications
            $table->enum('fuel_type', ['diesel', 'gasoline', 'electric', 'hybrid', 'petrol'])->default('diesel'); // Combined from both
            $table->enum('transmission', ['manual', 'automatic'])->default('manual'); // From first migration (more specific than second)
            $table->integer('mileage')->default(0); // From first migration (with default)
            $table->string('engine_capacity')->nullable(); // From second migration
            $table->integer('seats')->default(5); // From both migrations
            
            // Features and descriptions
            $table->json('features')->nullable(); // From second migration
            $table->text('description')->nullable(); // From both migrations
            
            // Ratings and reviews
            $table->decimal('rating', 3, 1)->default(0); // From second migration
            $table->integer('review_count')->default(0); // From second migration
            
            // Media
            $table->string('main_image')->nullable(); // From second migration
            
            // SEO fields
            $table->string('meta_title')->nullable(); // From second migration
            $table->text('meta_description')->nullable(); // From second migration
            $table->string('meta_keywords')->nullable(); // From second migration
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};