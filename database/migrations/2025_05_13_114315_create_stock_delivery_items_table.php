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
        Schema::create('stock_delivery_items', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('stock_delivery_id');
            $table->unsignedBigInteger('sales_order_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('lot_number')->nullable();
            $table->decimal('expected_quantity', 12, 3)->default(0);
            $table->decimal('delivered_quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2); // CMUP at the time of delivery
            $table->decimal('unit_price', 12, 2);
            $table->text('notes')->nullable();
            $table->foreign('stock_delivery_id')->references('id')->on('stock_deliveries')->onDelete('cascade');
            $table->foreign('sales_order_item_id')->references('id')->on('sales_order_items')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_delivery_items');
    }
};