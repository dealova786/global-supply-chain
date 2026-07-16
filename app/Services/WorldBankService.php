<?php

namespace App\Services;

use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Support\Facades\Http;

class WorldBankService
{
    public function getEconomicData(Country $country): array
    {
        if (!$country->cca2) {
            return $this->getCachedEconomy(
                $country,
                'Kode negara tidak tersedia. Menampilkan data cache jika ada.'
            );
        }

        try {
            $countryCode = strtolower($country->cca2);

            $gdp = $this->fetchLatestIndicatorValue($countryCode, 'NY.GDP.MKTP.CD');
            $inflation = $this->fetchLatestIndicatorValue($countryCode, 'FP.CPI.TOTL.ZG');
            $population = $this->fetchLatestIndicatorValue($countryCode, 'SP.POP.TOTL');
            $exports = $this->fetchLatestIndicatorValue($countryCode, 'NE.EXP.GNFS.CD');
            $imports = $this->fetchLatestIndicatorValue($countryCode, 'NE.IMP.GNFS.CD');

            if (!$gdp && !$inflation && !$population && !$exports && !$imports) {
                return $this->getCachedEconomy(
                    $country,
                    'World Bank API gagal memberikan data. Menampilkan data cache terakhir jika tersedia.'
                );
            }

            $year = $gdp['year']
                ?? $inflation['year']
                ?? $population['year']
                ?? $exports['year']
                ?? $imports['year']
                ?? now()->year;

            $economic = EconomicIndicator::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'year' => $year,
                ],
                [
                    'gdp' => $gdp['value'] ?? null,
                    'inflation' => $inflation['value'] ?? null,
                    'population' => $population['value'] ?? null,
                    'exports' => $exports['value'] ?? null,
                    'imports' => $imports['value'] ?? null,
                ]
            );

            return [
                'success' => true,
                'source' => 'api',
                'message' => 'Economic data retrieved successfully.',
                'data' => [
                    'year' => $economic->year,
                    'gdp' => $economic->gdp,
                    'inflation' => $economic->inflation,
                    'population' => $economic->population,
                    'exports' => $economic->exports,
                    'imports' => $economic->imports,
                ],
            ];

        } catch (\Throwable $e) {
            return $this->getCachedEconomy(
                $country,
                'World Bank API timeout atau koneksi gagal. Menampilkan data cache terakhir jika tersedia.'
            );
        }
    }

    private function fetchLatestIndicatorValue(string $countryCode, string $indicatorCode): ?array
    {
        try {
            $url = "https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicatorCode}";

            $response = Http::connectTimeout(4)
                ->timeout(8)
                ->retry(1, 500)
                ->get($url, [
                    'format' => 'json',
                    'per_page' => 5,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $json = $response->json();

            if (!isset($json[1]) || !is_array($json[1])) {
                return null;
            }

            foreach ($json[1] as $item) {
                if (isset($item['value']) && $item['value'] !== null) {
                    return [
                        'year' => (int) $item['date'],
                        'value' => $item['value'],
                    ];
                }
            }

            return null;

        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getCachedEconomy(Country $country, string $message): array
    {
        $cache = EconomicIndicator::where('country_id', $country->id)
            ->orderByDesc('year')
            ->latest()
            ->first();

        if (!$cache) {
            return [
                'success' => false,
                'source' => 'none',
                'message' => $message . ' Belum ada data ekonomi cache untuk negara ini.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'source' => 'cache',
            'message' => $message,
            'data' => [
                'year' => $cache->year,
                'gdp' => $cache->gdp,
                'inflation' => $cache->inflation,
                'population' => $cache->population,
                'exports' => $cache->exports,
                'imports' => $cache->imports,
            ],
        ];
    }
}