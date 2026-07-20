<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PortSyncService
{
    public function syncByCountry(Country $country): array
    {
        $username = config('services.geonames.username');
        $baseUrl = config('services.geonames.base_url', 'https://secure.geonames.org');

        if (!$username) {
            return [
                'status' => false,
                'message' => 'GeoNames username belum diisi di file .env.',
                'synced' => 0,
                'skipped' => 0,
            ];
        }

        if (!$country->cca2) {
            return [
                'status' => false,
                'message' => 'Kode negara tidak tersedia.',
                'synced' => 0,
                'skipped' => 0,
            ];
        }

        try {
            $featureCodes = [
                'PRT', 
                'HBR', 
            ];

            $synced = 0;
            $skipped = 0;
            $usedNames = [];

            foreach ($featureCodes as $featureCode) {
                $response = Http::connectTimeout(5)
                    ->timeout(15)
                    ->retry(1, 500)
                    ->get($baseUrl . '/searchJSON', [
                        'country' => strtoupper($country->cca2),
                        'featureCode' => $featureCode,
                        'maxRows' => 1000,
                        'style' => 'FULL',
                        'username' => $username,
                    ]);

                if (!$response->successful()) {
                    return [
                        'status' => false,
                        'message' => 'GeoNames API gagal merespons. HTTP Status: ' . $response->status(),
                        'synced' => $synced,
                        'skipped' => $skipped,
                    ];
                }

                $json = $response->json();

                if (isset($json['status'])) {
                    return [
                        'status' => false,
                        'message' => 'GeoNames API error: ' . ($json['status']['message'] ?? 'Unknown error'),
                        'synced' => $synced,
                        'skipped' => $skipped,
                    ];
                }

                $items = $json['geonames'] ?? [];

                foreach ($items as $item) {
                    $name = $item['name'] ?? $item['toponymName'] ?? null;
                    $lat = $item['lat'] ?? null;
                    $lng = $item['lng'] ?? null;

                    if (!$name || !$lat || !$lng) {
                        $skipped++;
                        continue;
                    }

                    $uniqueKey = strtolower($country->cca2 . '-' . $name . '-' . $lat . '-' . $lng);

                    if (in_array($uniqueKey, $usedNames)) {
                        $skipped++;
                        continue;
                    }

                    $usedNames[] = $uniqueKey;

                    Port::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'port_name' => Str::limit($name, 255, ''),
                        ],
                        [
                            'country_name' => $country->name,
                            'country_code' => $country->cca2,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'region' => $country->region,
                            'harbor_size' => $this->detectHarborSize($item),
                            'port_type' => $this->detectPortType($item),
                        ]
                    );

                    $synced++;
                }
            }

            return [
                'status' => true,
                'message' => 'Data port berhasil disinkronkan dari GeoNames API.',
                'synced' => $synced,
                'skipped' => $skipped,
            ];

        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Sync ports gagal: ' . $e->getMessage(),
                'synced' => 0,
                'skipped' => 0,
            ];
        }
    }

    private function detectPortType(array $item): string
    {
        $featureCode = $item['fcode'] ?? null;
        $featureName = strtolower($item['fcodeName'] ?? '');

        if ($featureCode === 'PRT') {
            return 'Port';
        }

        if ($featureCode === 'HBR' || str_contains($featureName, 'harbor')) {
            return 'Harbor';
        }

        return 'Port / Harbor';
    }

    private function detectHarborSize(array $item): string
    {
        $name = strtolower($item['name'] ?? '');

        if (str_contains($name, 'international') || str_contains($name, 'container')) {
            return 'Large';
        }

        if (str_contains($name, 'port')) {
            return 'Medium';
        }

        return 'Unknown';
    }
}