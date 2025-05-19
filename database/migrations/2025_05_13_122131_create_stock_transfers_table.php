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
        Schema::create('stock_transfers', function (Blueprint $table) {
             $table->id();
            $table->string('reference_no')->unique();
            $table->unsignedBigInteger('source_warehouse_id');
            $table->unsignedBigInteger('destination_warehouse_id');
            $table->unsignedBigInteger('created_by');
            $table->date('transfer_date');
            $table->string('status'); // draft, in_transit, completed, cancelled
            $table->text('notes')->nullable();
            $table->foreign('source_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('destination_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};