<?php

namespace App\Services;

use App\Models\Country;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public function getCurrencyRate(Country $country, string $targetCurrency = 'USD'): array
    {
        $baseCurrency = $country->currency_code;

        if (!$baseCurrency) {
            return [
                'success' => false,
                'message' => 'Country currency code not found',
                'data' => null,
            ];
        }

        $response = Http::timeout(10)->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Failed to fetch currency data',
                'data' => null,
            ];
        }

        $data = $response->json();

        if (!isset($data['rates'][$targetCurrency])) {
            return [
                'success' => false,
                'message' => 'Target currency not found',
                'data' => null,
            ];
        }

        $exchangeRate = $data['rates'][$targetCurrency];
        $rateDate = now()->toDateString();

        $previousRate = CurrencyRate::where('country_id', $country->id)
            ->where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency)
            ->latest()
            ->first();

        $currencyRisk = $this->calculateCurrencyRisk($exchangeRate, $previousRate?->exchange_rate);

        CurrencyRate::create([
            'country_id' => $country->id,
            'base_currency' => $baseCurrency,
            'target_currency' => $targetCurrency,
            'exchange_rate' => $exchangeRate,
            'currency_risk' => $currencyRisk,
            'rate_date' => $rateDate,
        ]);

        return [
            'success' => true,
            'message' => 'Currency data retrieved successfully',
            'data' => [
                'base_currency' => $baseCurrency,
                'target_currency' => $targetCurrency,
                'exchange_rate' => $exchangeRate,
                'currency_risk' => $currencyRisk,
                'rate_date' => $rateDate,
            ],
        ];
    }

    private function calculateCurrencyRisk(float $currentRate, ?float $previousRate): float
    {
        if (!$previousRate || $previousRate == 0) {
            return 20;
        }

        $changePercentage = abs((($currentRate - $previousRate) / $previousRate) * 100);

        if ($changePercentage >= 5) {
            return 80;
        }

        if ($changePercentage >= 3) {
            return 60;
        }

        if ($changePercentage >= 1) {
            return 40;
        }

        return 20;
    }
}
