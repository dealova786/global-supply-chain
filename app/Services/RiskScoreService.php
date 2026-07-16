<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;

class RiskScoreService
{
    public function calculate(
        Country $country,
        ?array $weatherData,
        ?array $economicData,
        ?array $currencyData,
        $newsData = []
    ): array {
        
        if ($newsData instanceof \Illuminate\Support\Collection) {
            $newsData = $newsData->toArray();
        }

        $weatherRisk = $weatherData['weather_risk'] ?? 20;

        $inflationRisk = $this->calculateInflationRisk(
            $economicData['inflation'] ?? null
        );

        $currencyRisk = $currencyData['currency_risk'] ?? 20;

        $newsRisk = $this->calculateNewsRisk($newsData);

        $totalScore =
            ($weatherRisk * 0.30) +
            ($inflationRisk * 0.20) +
            ($newsRisk * 0.40) +
            ($currencyRisk * 0.10);

        $totalScore = round($totalScore, 2);

        $riskLevel = $this->determineRiskLevel($totalScore);

        RiskScore::create([
            'country_id' => $country->id,
            'weather_risk' => $weatherRisk,
            'inflation_risk' => $inflationRisk,
            'currency_risk' => $currencyRisk,
            'news_risk' => $newsRisk,
            'total_score' => $totalScore,
            'risk_level' => $riskLevel,
            'calculated_at' => now(),
        ]);

        return [
            'weather_risk' => $weatherRisk,
            'inflation_risk' => $inflationRisk,
            'currency_risk' => $currencyRisk,
            'news_risk' => $newsRisk,
            'total_score' => $totalScore,
            'risk_level' => $riskLevel,
            'calculated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function calculateInflationRisk(?float $inflation): int
    {
        if ($inflation === null) {
            return 50;
        }

        if ($inflation <= 3) {
            return 20;
        }

        if ($inflation <= 7) {
            return 50;
        }

        return 80;
    }

    private function calculateNewsRisk(array $newsData): int
    {
        if (empty($newsData)) {
            return 50;
        }

        $totalRisk = 0;
        $count = 0;

        foreach ($newsData as $news) {
            if (isset($news['news_risk'])) {
                $totalRisk += $news['news_risk'];
                $count++;
            }
        }

        if ($count === 0) {
            return 50;
        }

        return round($totalRisk / $count);
    }

    private function determineRiskLevel(float $score): string
    {
        if ($score <= 30) {
            return 'Low';
        }

        if ($score <= 60) {
            return 'Medium';
        }

        return 'High';
    }
}