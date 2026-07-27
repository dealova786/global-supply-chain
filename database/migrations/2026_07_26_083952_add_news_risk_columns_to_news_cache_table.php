<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('news_cache')) {
            return;
        }

        Schema::table('news_cache', function (Blueprint $table) {
            if (!Schema::hasColumn('news_cache', 'positive_score')) {
                $table->integer('positive_score')->default(0)->after('sentiment');
            }

            if (!Schema::hasColumn('news_cache', 'negative_score')) {
                $table->integer('negative_score')->default(0)->after('positive_score');
            }

            if (!Schema::hasColumn('news_cache', 'news_risk')) {
                $table->integer('news_risk')->default(0)->after('negative_score');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('news_cache')) {
            return;
        }

        Schema::table('news_cache', function (Blueprint $table) {
            if (Schema::hasColumn('news_cache', 'news_risk')) {
                $table->dropColumn('news_risk');
            }

            if (Schema::hasColumn('news_cache', 'negative_score')) {
                $table->dropColumn('negative_score');
            }

            if (Schema::hasColumn('news_cache', 'positive_score')) {
                $table->dropColumn('positive_score');
            }
        });
    }
};