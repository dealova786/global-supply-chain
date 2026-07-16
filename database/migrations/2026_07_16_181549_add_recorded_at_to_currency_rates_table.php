<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('currency_rates', 'recorded_at')) {
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->timestamp('recorded_at')->nullable();
            });
        }

        DB::table('currency_rates')
            ->whereNull('recorded_at')
            ->update([
                'recorded_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('currency_rates', 'recorded_at')) {
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->dropColumn('recorded_at');
            });
        }
    }
};