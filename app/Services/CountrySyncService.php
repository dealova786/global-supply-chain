<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;

class CountrySyncService
{
    public function sync(): array
    {
        $apiKey = config('services.restcountries.api_key');
        $baseUrl = config('services.restcountries.base_url', 'https://api.restcountries.com/countries/v5');

        if (!$apiKey) {
            return [
                'status' => false,
                'message' => 'REST Countries API key belum diisi di file .env.',
                'synced' => 0,
                'skipped' => 0,
                'errors' => ['Isi REST_COUNTRIES_API_KEY di file .env'],
            ];
        }

        $totalSynced = 0;
        $totalSkipped = 0;
        $errors = [];

        $limit = 100;
        $offset = 0;

        try {
            do {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->connectTimeout(30)
                    ->timeout(120)
                    ->retry(2, 2000)
                    ->get($baseUrl, [
                        'limit' => $limit,
                        'offset' => $offset,
                        'response_fields' => 'names,codes,capitals,region,subregion,currencies,languages,population,flag',
                    ]);

                if (!$response->successful()) {
                    $errors[] = 'HTTP Status: ' . $response->status() . ' - ' . $response->body();
                    break;
                }

                $json = $response->json();

                if (isset($json['errors'])) {
                    $errors[] = json_encode($json['errors']);
                    break;
                }

                $objects = $json['data']['objects'] ?? [];
                $meta = $json['data']['meta'] ?? [];

                if (!is_array($objects) || count($objects) === 0) {
                    $errors[] = 'Response kosong atau format data.objects tidak ditemukan.';
                    break;
                }

                foreach ($objects as $item) {
                    $name = $item['names']['common'] ?? null;
                    $officialName = $item['names']['official'] ?? $name;

                    $cca2 = $item['codes']['alpha_2'] ?? null;
                    $cca3 = $item['codes']['alpha_3'] ?? null;

                    if (!$name || !$cca2) {
                        $totalSkipped++;
                        continue;
                    }

                    $capital = $item['capitals'][0]['name'] ?? null;

                    $latitude = $item['capitals'][0]['coordinates']['lat'] ?? null;
                    $longitude = $item['capitals'][0]['coordinates']['lng'] ?? null;

                    $currencyCode = $item['currencies'][0]['code'] ?? null;
                    $currencyName = $item['currencies'][0]['name'] ?? null;

                    $language = $item['languages'][0]['name'] ?? null;

                    $flagUrl = $item['flag']['url_png'] ?? null;

                    if (!$flagUrl && $cca2) {
                        $flagUrl = 'https://flagcdn.com/w320/' . strtolower($cca2) . '.png';
                    }

                    Country::updateOrCreate(
                        [
                            'cca2' => $cca2,
                        ],
                        [
                            'name' => $name,
                            'official_name' => $officialName,
                            'cca3' => $cca3,
                            'capital' => $capital,
                            'region' => $item['region'] ?? null,
                            'subregion' => $item['subregion'] ?? null,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'currency_code' => $currencyCode,
                            'currency_name' => $currencyName,
                            'language' => $language,
                            'population' => $item['population'] ?? 0,
                            'flag_url' => $flagUrl,
                        ]
                    );

                    $totalSynced++;
                }

                $more = $meta['more'] ?? false;
                $offset += $limit;

            } while ($more);

            return [
                'status' => $totalSynced > 0,
                'message' => $totalSynced > 0
                    ? 'Data negara global berhasil disinkronkan dari REST Countries API v5.'
                    : 'Sync countries gagal. Tidak ada data yang berhasil masuk.',
                'synced' => $totalSynced,
                'skipped' => $totalSkipped,
                'errors' => $errors,
            ];

        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Sync countries gagal: ' . $e->getMessage(),
                'synced' => $totalSynced,
                'skipped' => $totalSkipped,
                'errors' => $errors,
            ];
        }
    }
}