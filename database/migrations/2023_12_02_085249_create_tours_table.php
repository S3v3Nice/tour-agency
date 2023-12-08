<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')
                ->constrained('tour_hotels')
                ->cascadeOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedSmallInteger('max_participant_count');
            $table->unsignedFloat('adult_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
