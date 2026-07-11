<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

class NegativeWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'disaster',
            'conflict',
            'shortage',
            'strike',
            'congestion',
            'decline',
            'risk',
            'loss',
            'collapse',
            'recession',
            'sanction',
            'instability',
            'disruption',
            'storm',
            'flood',
            'earthquake',
            'attack',
            'tariff',
            'embargo',
            'decrease',
            'negative',
        ];

        foreach ($words as $word) {
            NegativeWord::updateOrCreate(
                ['word' => $word],
                ['word' => $word]
            );
        }
    }
}