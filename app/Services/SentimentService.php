<?php

namespace App\Services;

use App\Models\PositiveWord;
use App\Models\NegativeWord;

class SentimentService
{
    public function analyze(string $text): array
    {
        $positiveWords = PositiveWord::pluck('word')
            ->map(fn ($word) => strtolower($word))
            ->toArray();

        $negativeWords = NegativeWord::pluck('word')
            ->map(fn ($word) => strtolower($word))
            ->toArray();

        $words = $this->tokenize($text);

        $positiveScore = 0;
        $negativeScore = 0;

        $matchedPositiveWords = [];
        $matchedNegativeWords = [];

        foreach ($words as $word) {
            $normalizedWord = $this->normalizeWord($word);

            if (in_array($word, $positiveWords) || in_array($normalizedWord, $positiveWords)) {
                $positiveScore++;
                $matchedPositiveWords[] = $word;
            }

            if (in_array($word, $negativeWords) || in_array($normalizedWord, $negativeWords)) {
                $negativeScore++;
                $matchedNegativeWords[] = $word;
            }
        }

        $totalMatched = $positiveScore + $negativeScore;
        $totalWords = count($words);

        $neutralScore = max($totalWords - $totalMatched, 0);

        $sentiment = $this->determineSentiment($positiveScore, $negativeScore);
        $newsRisk = $this->calculateNewsRisk($sentiment, $negativeScore, $positiveScore);

        return [
            'text' => $text,
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'neutral_score' => $neutralScore,
            'sentiment' => $sentiment,
            'news_risk' => $newsRisk,
            'matched_positive_words' => array_values(array_unique($matchedPositiveWords)),
            'matched_negative_words' => array_values(array_unique($matchedNegativeWords)),
            'percentage' => [
                'positive' => $this->percentage($positiveScore, $totalWords),
                'negative' => $this->percentage($negativeScore, $totalWords),
                'neutral' => $this->percentage($neutralScore, $totalWords),
            ],
        ];
    }

    private function tokenize(string $text): array
    {
        $text = strtolower($text);

        $text = preg_replace('/[^a-zA-Z\s]/', ' ', $text);

        $words = preg_split('/\s+/', $text);

        return array_values(array_filter($words));
    }

    private function normalizeWord(string $word): string
    {
        if (str_ends_with($word, 'ing')) {
            return substr($word, 0, -3);
        }

        if (str_ends_with($word, 'ed')) {
            return substr($word, 0, -2);
        }

        if (str_ends_with($word, 'es')) {
            return substr($word, 0, -2);
        }

        if (str_ends_with($word, 's')) {
            return substr($word, 0, -1);
        }

        return $word;
    }

    private function determineSentiment(int $positiveScore, int $negativeScore): string
    {
        if ($positiveScore > $negativeScore) {
            return 'Positive';
        }

        if ($negativeScore > $positiveScore) {
            return 'Negative';
        }

        return 'Neutral';
    }

    private function calculateNewsRisk(string $sentiment, int $negativeScore, int $positiveScore): int
    {
        if ($sentiment === 'Negative') {
            if ($negativeScore >= 3) {
                return 80;
            }

            return 60;
        }

        if ($sentiment === 'Neutral') {
            return 50;
        }

        return 20;
    }

    private function percentage(int $score, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($score / $total) * 100, 2);
    }
}