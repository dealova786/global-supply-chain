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
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained('countries')
                ->onDelete('cascade');

            $table->decimal('weather_risk', 8, 2)->default(0);
            $table->decimal('inflation_risk', 8, 2)->default(0);
            $table->decimal('currency_risk', 8, 2)->default(0);
            $table->decimal('news_risk', 8, 2)->default(0);

            $table->decimal('total_score', 8, 2)->default(0);
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->default('Low');

            $table->dateTime('calculated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};
