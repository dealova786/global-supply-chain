<?php

namespace App\Services;

use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Support\Facades\Http;

class WorldBankService
{
    public function getEconomicData(Country $country): array
    {
        $countryCode = strtolower($country->cca2);

        if (!$countryCode) {
            return $this->getCachedEconomicData($country, 'Kode negara tidak tersedia.');
        }

        try {
            $gdp = $this->fetchLatestIndicatorValue($countryCode, 'NY.GDP.MKTP.CD');
            $inflation = $this->fetchLatestIndicatorValue($countryCode, 'FP.CPI.TOTL.ZG');
            $population = $this->fetchLatestIndicatorValue($countryCode, 'SP.POP.TOTL');
            $exports = $this->fetchLatestIndicatorValue($countryCode, 'NE.EXP.GNFS.CD');
            $imports = $this->fetchLatestIndicatorValue($countryCode, 'NE.IMP.GNFS.CD');

            $year = collect([
                $gdp['year'] ?? null,
                $inflation['year'] ?? null,
                $population['year'] ?? null,
                $exports['year'] ?? null,
                $imports['year'] ?? null,
            ])->filter()->max();

            if (
                is_null($gdp['value']) &&
                is_null($inflation['value']) &&
                is_null($population['value']) &&
                is_null($exports['value']) &&
                is_null($imports['value'])
            ) {
                return $this->getCachedEconomicData($country, 'World Bank API tidak menemukan data ekonomi untuk negara ini.');
            }

            $economic = EconomicIndicator::create([
                'country_id' => $country->id,
                'gdp' => $gdp['value'],
                'inflation' => $inflation['value'],
                'population' => $population['value'],
                'exports' => $exports['value'],
                'imports' => $imports['value'],
                'year' => $year,
            ]);

            return [
                'success' => true,
                'source' => 'api',
                'message' => 'Economic data retrieved successfully from World Bank API.',
                'data' => [
                    'gdp' => $economic->gdp,
                    'inflation' => $economic->inflation,
                    'population' => $economic->population,
                    'exports' => $economic->exports,
                    'imports' => $economic->imports,
                    'year' => $economic->year,
                    'gdp_year' => $gdp['year'],
                    'inflation_year' => $inflation['year'],
                    'population_year' => $population['year'],
                    'exports_year' => $exports['year'],
                    'imports_year' => $imports['year'],
                ],
            ];
        } catch (\Throwable $e) {
            return $this->getCachedEconomicData(
                $country,
                'World Bank API timeout atau koneksi gagal. Menampilkan cache terakhir jika tersedia.'
            );
        }
    }

    private function fetchLatestIndicatorValue(string $countryCode, string $indicatorCode): array
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 500)
                ->get("https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicatorCode}", [
                    'format' => 'json',
                    'per_page' => 80,
                ]);

            if (!$response->successful()) {
                return [
                    'value' => null,
                    'year' => null,
                ];
            }

            $json = $response->json();

            $records = $json[1] ?? [];

            foreach ($records as $record) {
                if (array_key_exists('value', $record) && !is_null($record['value'])) {
                    return [
                        'value' => $record['value'],
                        'year' => $record['date'] ?? null,
                    ];
                }
            }

            return [
                'value' => null,
                'year' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'value' => null,
                'year' => null,
            ];
        }
    }

    private function getCachedEconomicData(Country $country, string $message): array
    {
        $cache = EconomicIndicator::where('country_id', $country->id)
            ->latest('year')
            ->latest('id')
            ->first();

        if (!$cache) {
            return [
                'success' => false,
                'source' => 'none',
                'message' => $message . ' Belum ada cache data ekonomi untuk negara ini.',
                'data' => [
                    'gdp' => null,
                    'inflation' => null,
                    'population' => $country->population,
                    'exports' => null,
                    'imports' => null,
                    'year' => null,
                ],
            ];
        }

        return [
            'success' => true,
            'source' => 'cache',
            'message' => $message,
            'data' => [
                'gdp' => $cache->gdp,
                'inflation' => $cache->inflation,
                'population' => $cache->population,
                'exports' => $cache->exports,
                'imports' => $cache->imports,
                'year' => $cache->year,
            ],
        ];
    }
}