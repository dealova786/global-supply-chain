<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositiveWordSeeder::class,
            NegativeWordSeeder::class,
            CountrySeeder::class,
            PortSeeder::class,
        ]);
    }
}