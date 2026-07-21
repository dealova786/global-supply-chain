<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('news_cache')) {
            return;
        }

        if (!Schema::hasColumn('news_cache', 'url')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {

            $indexes = DB::select("SHOW INDEX FROM news_cache WHERE Column_name = 'url'");

            $indexNames = collect($indexes)
                ->pluck('Key_name')
                ->filter()
                ->unique()
                ->reject(function ($indexName) {
                    return $indexName === 'PRIMARY';
                });

            foreach ($indexNames as $indexName) {
                $safeIndexName = str_replace('`', '', $indexName);

                try {
                    DB::statement("ALTER TABLE news_cache DROP INDEX `{$safeIndexName}`");
                } catch (\Throwable $e) {

                }
            }

            DB::statement('ALTER TABLE news_cache MODIFY url TEXT NULL');
        } catch (\Throwable $e) {

            try {
                DB::statement('ALTER TABLE news_cache MODIFY url VARCHAR(1000) NULL');
            } catch (\Throwable $e) {
                // Abaikan agar deploy tetap lanjut.
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('news_cache')) {
            return;
        }

        if (!Schema::hasColumn('news_cache', 'url')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::statement('ALTER TABLE news_cache MODIFY url VARCHAR(255) NULL');
        } catch (\Throwable $e) {

        }
    }
};