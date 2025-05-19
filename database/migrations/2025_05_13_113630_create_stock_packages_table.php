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
        Schema::create('stock_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('lot_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost', 12, 2); // Unit cost when this package was added
            $table->boolean('available')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('stock_reception_item_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('stock_reception_item_id')->references('id')->on('stock_reception_items')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_packages');
    }
};