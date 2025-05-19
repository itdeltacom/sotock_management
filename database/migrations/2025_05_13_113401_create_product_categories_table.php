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
        Schema::create('product_categories', function (Blueprint $table) {
          $table->id();
    $table->string('name', 100);
    $table->string('slug')->unique();
    $table->string('code', 50)->nullable()->unique();
    $table->string('website', 255)->nullable();
    $table->string('logo')->nullable();
    $table->text('description')->nullable();
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->text('meta_keywords')->nullable();
    $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('restrict');
    $table->boolean('active')->default(true);
    $table->timestamps();
    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};