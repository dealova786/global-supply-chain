<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
{
    Schema::create('weather_cache', function (Blueprint $table) {
        $table->id();

        $table->foreignId('country_id')
              ->constrained('countries')
              ->onDelete('cascade');

        $table->decimal('temperature', 8, 2)->nullable();
        $table->decimal('rainfall', 8, 2)->nullable();
        $table->decimal('wind_speed', 8, 2)->nullable();

        $table->integer('weather_risk')->default(0);
        $table->dateTime('recorded_at')->nullable();

        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('weather_cache');
    }
};
