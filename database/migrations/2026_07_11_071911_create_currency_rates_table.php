<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('currency_rates', function (Blueprint $table) {
        $table->id();

        $table->foreignId('country_id')
              ->constrained('countries')
              ->onDelete('cascade');

        $table->string('base_currency', 10)->nullable();
        $table->string('target_currency', 10)->default('USD');
        $table->decimal('exchange_rate', 15, 6)->nullable();

        $table->decimal('currency_risk', 8, 2)->default(0);
        $table->date('rate_date')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
