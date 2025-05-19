<?php

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
        Schema::create('products', function (Blueprint $table) {
             $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('piece'); // unit of measurement
            $table->string('barcode')->nullable()->unique();
            $table->string('sku')->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->string('image')->nullable();
            $table->json('attributes')->nullable(); // For additional dynamic attributes
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};