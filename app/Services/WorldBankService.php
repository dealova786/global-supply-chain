<?php

namespace App\Services;

use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Support\Facades\Http;

class WorldBankService
{
    private array $indicators = [
        'gdp' => 'NY.GDP.MKTP.CD',
        'inflation' => 'FP.CPI.TOTL.ZG',
        'population' => 'SP.POP.TOTL',
        'exports' => 'NE.EXP.GNFS.CD',
        'imports' => 'NE.IMP.GNFS.CD',
    ];

    public function getEconomicData(Country $country): array
    {
        $countryCode = strtolower($country->cca2);

        $result = [
            'year' => null,
            'gdp' => null,
            'inflation' => null,
            'population' => null,
            'exports' => null,
            'imports' => null,
        ];

        foreach ($this->indicators as $key => $indicatorCode) {
            $value = $this->fetchLatestIndicatorValue($countryCode, $indicatorCode);

            if ($value) {
                $result[$key] = $value['value'];

                if ($result['year'] === null || $value['year'] > $result['year']) {
                    $result['year'] = $value['year'];
                }
            }
        }

        EconomicIndicator::updateOrCreate(
            [
                'country_id' => $country->id,
                'year' => $result['year'],
            ],
            [
                'gdp' => $result['gdp'],
                'inflation' => $result['inflation'],
                'population' => $result['population'],
                'exports' => $result['exports'],
                'imports' => $result['imports'],
            ]
        );

        return [
            'success' => true,
            'message' => 'Economic data retrieved successfully',
            'data' => $result,
        ];
    }

    private function fetchLatestIndicatorValue(string $countryCode, string $indicatorCode): ?array
    {
        try {
            $url = "https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicatorCode}";

            $response = Http::connectTimeout(3)
                ->timeout(6)
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
}