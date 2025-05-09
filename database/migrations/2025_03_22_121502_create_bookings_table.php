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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('booking_number')->unique();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->date('pickup_date');
            $table->string('pickup_time');
            $table->date('dropoff_date');
            $table->string('dropoff_time');
            $table->integer('total_days');
            $table->decimal('base_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); 
            $table->string('payment_status')->default('unpaid'); 
            $table->string('payment_method')->nullable(); 
            $table->text('special_requests')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_id_number')->nullable(); // Moroccan CIN or passport
            $table->string('transaction_id')->nullable();
            $table->string('insurance_plan')->nullable(); 
            $table->boolean('additional_driver')->default(false);
            $table->string('additional_driver_name')->nullable();
            $table->string('additional_driver_license')->nullable();
            $table->string('delivery_option')->nullable(); 
            $table->text('delivery_address')->nullable();
            $table->string('fuel_policy')->default('full-to-full'); 
            $table->integer('mileage_limit')->nullable(); 
            $table->decimal('extra_mileage_cost', 8, 2)->nullable(); 
            $table->decimal('deposit_amount', 10, 2)->nullable(); 
            $table->string('deposit_status')->default('pending'); 
            $table->text('notes')->nullable(); // Internal staff notes
            $table->text('cancellation_reason')->nullable();
            $table->string('confirmation_code')->nullable()->unique();
            $table->string('language_preference')->default('fr'); // ar, fr, en
            $table->boolean('gps_enabled')->default(false);
            $table->boolean('child_seat')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};