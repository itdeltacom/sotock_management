<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('maintenance_type');
            $table->date('date_performed');
            $table->date('next_due_date')->nullable();
            $table->integer('next_due_mileage')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->integer('mileage_at_service');
            $table->string('oil_type')->nullable();
            $table->string('oil_quantity')->nullable();
            $table->boolean('is_completed')->default(true);
            $table->text('parts_replaced')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_maintenances');
    }
};