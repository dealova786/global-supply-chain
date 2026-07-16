<?php

namespace App\Services;

use App\Models\Country;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public function getCurrencyData(Country $country): array
    {
        return $this->fetchCurrencyData($country);
    }

    public function getExchangeRate(Country $country): array
    {
        return $this->fetchCurrencyData($country);
    }

    public function getCurrencyRate(Country $country): array
    {
        return $this->fetchCurrencyData($country);
    }

    private function fetchCurrencyData(Country $country): array
    {
        $baseCurrency = $country->currency_code;
        $targetCurrency = 'USD';

        if (!$baseCurrency) {
            return $this->getCachedCurrency(
                $country,
                'Kode mata uang negara tidak tersedia. Menampilkan cache jika ada.'
            );
        }

        try {
            if ($baseCurrency === $targetCurrency) {
                $exchangeRate = 1;
            } else {
                $response = Http::connectTimeout(5)
                    ->timeout(8)
                    ->retry(1, 500)
                    ->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

                if (!$response->successful()) {
                    return $this->getCachedCurrency(
                        $country,
                        'Currency API gagal merespons. Menampilkan cache terakhir jika tersedia.'
                    );
                }

                $json = $response->json();

                if (($json['result'] ?? null) !== 'success') {
                    return $this->getCachedCurrency(
                        $country,
                        'Currency API tidak berhasil mengambil data. Menampilkan cache terakhir jika tersedia.'
                    );
                }

                $exchangeRate = $json['rates'][$targetCurrency] ?? null;

                if (!$exchangeRate) {
                    return $this->getCachedCurrency(
                        $country,
                        'Rate USD tidak tersedia untuk mata uang ini. Menampilkan cache terakhir jika tersedia.'
                    );
                }
            }

            $previousRate = CurrencyRate::where('country_id', $country->id)
                ->where('base_currency', $baseCurrency)
                ->where('target_currency', $targetCurrency)
                ->latest('recorded_at')
                ->first();

            $currencyRisk = $this->calculateCurrencyRisk(
                $previousRate?->exchange_rate,
                $exchangeRate
            );

            $currency = CurrencyRate::create([
                'country_id' => $country->id,
                'base_currency' => $baseCurrency,
                'target_currency' => $targetCurrency,
                'exchange_rate' => $exchangeRate,
                'currency_risk' => $currencyRisk,
                'recorded_at' => now(),
            ]);

            return [
                'success' => true,
                'source' => 'api',
                'message' => 'Currency data retrieved successfully.',
                'data' => [
                    'base_currency' => $currency->base_currency,
                    'target_currency' => $currency->target_currency,
                    'exchange_rate' => $currency->exchange_rate,
                    'currency_risk' => $currency->currency_risk,
                    'recorded_at' => $currency->recorded_at ?? $currency->created_at,
                    'rate_date' => $currency->recorded_at ?? $currency->created_at,
                ],
            ];

        } catch (\Throwable $e) {
            return $this->getCachedCurrency(
                $country,
                'Currency API timeout atau koneksi gagal. Menampilkan cache terakhir jika tersedia.'
            );
        }
    }

    private function getCachedCurrency(Country $country, string $message): array
    {
        $cache = CurrencyRate::where('country_id', $country->id)
            ->latest('recorded_at')
            ->first();

        if (!$cache) {
            return [
                'success' => false,
                'source' => 'none',
                'message' => $message . ' Belum ada cache currency untuk negara ini.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'source' => 'cache',
            'message' => $message,
            'data' => [
                'base_currency' => $cache->base_currency,
                'target_currency' => $cache->target_currency,
                'exchange_rate' => $cache->exchange_rate,
                'currency_risk' => $cache->currency_risk,
                'recorded_at' => $cache->recorded_at ?? $cache->created_at,
                'rate_date' => $cache->recorded_at ?? $cache->created_at,
            ],
        ];
    }

    private function calculateCurrencyRisk($previousRate, $currentRate): int
    {
        if (!$previousRate || !$currentRate) {
            return 20;
        }

        $changePercentage = abs(($currentRate - $previousRate) / $previousRate) * 100;

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