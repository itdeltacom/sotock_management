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
        Schema::create('car_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('maintenance_type');
            $table->date('date_performed');
            $table->integer('mileage_at_service');
            $table->date('next_due_date')->nullable();
            $table->integer('next_due_mileage')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('oil_type')->nullable();
            $table->string('oil_quantity')->nullable();
            $table->string('parts_replaced')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('maintenance_type');
            $table->index('date_performed');
            $table->index('next_due_date');
            $table->index('next_due_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_maintenances');
    }
};