<?php

use App\Models\TourCountry;
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
        Schema::create('tour_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                  ->constrained('tour_countries')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_cities');
    }
};
