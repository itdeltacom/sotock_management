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
        Schema::create('stock_deliveries', function (Blueprint $table) {
              $table->id();
            $table->string('reference_no')->unique();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('delivered_by');
            $table->date('delivery_date');
            $table->string('status'); // pending, completed, partial
            $table->text('notes')->nullable();
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('delivered_by')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_deliveries');
    }
};