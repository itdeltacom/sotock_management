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
        Schema::create('stock_reception_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_reception_id');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('expected_quantity', 12, 3)->default(0);
            $table->decimal('received_quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreign('stock_reception_id')->references('id')->on('stock_receptions')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reception_items');
    }
};