<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('economic_indicators', function (Blueprint $table) {
        $table->id();

        $table->foreignId('country_id')
              ->constrained('countries')
              ->onDelete('cascade');

        $table->year('year')->nullable();

        $table->decimal('gdp', 20, 2)->nullable();
        $table->decimal('inflation', 10, 2)->nullable();
        $table->bigInteger('population')->nullable();
        $table->decimal('exports', 20, 2)->nullable();
        $table->decimal('imports', 20, 2)->nullable();

        $table->timestamps();
    });

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
