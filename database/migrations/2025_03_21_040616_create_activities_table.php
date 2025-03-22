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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // booking, customer, vehicle, admin, error, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->nullableMorphs('user'); // Who performed the activity
            $table->nullableMorphs('subject'); // What the activity is about
            $table->json('properties')->nullable(); // Any additional data
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};