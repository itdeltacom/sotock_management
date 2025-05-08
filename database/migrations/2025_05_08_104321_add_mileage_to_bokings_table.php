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
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('start_mileage')->nullable()->after('completed_at');
            $table->integer('end_mileage')->nullable()->after('start_mileage');
            $table->integer('extra_mileage')->nullable()->after('end_mileage');
            $table->decimal('extra_mileage_charges', 10, 2)->nullable()->after('extra_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'start_mileage',
                'end_mileage',
                'extra_mileage',
                'extra_mileage_charges'
            ]);
        });
    }
};