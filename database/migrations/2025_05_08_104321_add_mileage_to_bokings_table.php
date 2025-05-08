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
        Schema::table('bokings', function (Blueprint $table) {
            $table->decimal('start_mileage', 10, 2)->nullable()->after('mileage_limit');
            $table->decimal('end_mileage', 10, 2)->nullable()->after('start_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bokings', function (Blueprint $table) {
            $table->dropColumn('start_mileage');
            $table->dropColumn('end_mileage');
        });
    }
};