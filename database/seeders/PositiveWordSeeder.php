<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class PositiveWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'growth',
            'increase',
            'profit',
            'stable',
            'improve',
            'recovery',
            'strong',
            'surplus',
            'expansion',
            'safe',
            'secure',
            'rise',
            'boost',
            'gain',
            'positive',
            'agreement',
            'cooperation',
            'export',
            'investment',
            'opportunity',
        ];

        foreach ($words as $word) {
            PositiveWord::updateOrCreate(
                ['word' => $word],
                ['word' => $word]
            );
        }
    }
}