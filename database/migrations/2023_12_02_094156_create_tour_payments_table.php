<?php

use App\Models\TourBooking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tour_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained('tour_bookings')
                  ->cascadeOnDelete();
            $table->unsignedFloat('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_payments');
    }
};
