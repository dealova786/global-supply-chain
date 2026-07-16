<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getCurrentWeather(Country $country): array
    {
        if (!$country->latitude || !$country->longitude) {
            return $this->getCachedWeather(
                $country,
                'Koordinat negara tidak tersedia. Menampilkan data cache jika ada.'
            );
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(8)
                ->retry(1, 500)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    'current' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                    'daily' => 'precipitation_sum',
                    'forecast_days' => 1,
                    'timezone' => 'auto',
                ]);

            if (!$response->successful()) {
                return $this->getCachedWeather(
                    $country,
                    'Weather API gagal merespons. Menampilkan data cache terakhir.'
                );
            }

            $json = $response->json();
            $current = $json['current'] ?? null;

            if (!$current) {
                return $this->getCachedWeather(
                    $country,
                    'Format data weather tidak valid. Menampilkan data cache terakhir.'
                );
            }

            $temperature = $current['temperature_2m'] ?? null;
            $currentPrecipitation = $current['precipitation'] ?? 0;
            $dailyPrecipitation = $json['daily']['precipitation_sum'][0] ?? $currentPrecipitation;

            $rainfall = $dailyPrecipitation;

            $windSpeed = $current['wind_speed_10m'] ?? 0;
            $weatherCode = $current['weather_code'] ?? null;

            $weatherCondition = $this->getWeatherCondition($weatherCode);

            $weatherRisk = $this->calculateWeatherRisk(
                $temperature,
                $rainfall,
                $windSpeed,
                $weatherCode
            );

            WeatherCache::create([
                'country_id' => $country->id,
                'temperature' => $temperature,
                'rainfall' => $rainfall,
                'wind_speed' => $windSpeed,
                'weather_code' => $weatherCode,
                'weather_condition' => $weatherCondition,
                'weather_risk' => $weatherRisk,
                'recorded_at' => now(),
            ]);

            return [
                'success' => true,
                'source' => 'api',
                'message' => 'Current weather and daily precipitation retrieved successfully.',
                'data' => [
                    'temperature' => $temperature,
                    'rainfall' => $rainfall,
                    'wind_speed' => $windSpeed,
                    'weather_code' => $weatherCode,
                    'weather_condition' => $weatherCondition,
                    'weather_risk' => $weatherRisk,
                    'recorded_at' => now(),
                ],
            ];

        } catch (\Throwable $e) {
            return $this->getCachedWeather(
                $country,
                'Weather API timeout atau koneksi gagal. Menampilkan data cache terakhir jika tersedia.'
            );
        }
    }

    private function getCachedWeather(Country $country, string $message): array
    {
        $cache = WeatherCache::where('country_id', $country->id)
            ->latest('recorded_at')
            ->first();

        if (!$cache) {
            return [
                'success' => false,
                'source' => 'none',
                'message' => $message . ' Belum ada data weather cache untuk negara ini.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'source' => 'cache',
            'message' => $message,
            'data' => [
                'temperature' => $cache->temperature,
                'rainfall' => $cache->rainfall,
                'wind_speed' => $cache->wind_speed,
                'weather_code' => $cache->weather_code,
                'weather_condition' => $cache->weather_condition,
                'weather_risk' => $cache->weather_risk,
                'recorded_at' => $cache->recorded_at,
            ],
        ];
    }

    private function getWeatherCondition(?int $weatherCode): string
    {
        return match (true) {
            $weatherCode === 0 => 'Clear Sky',
            in_array($weatherCode, [1, 2, 3]) => 'Cloudy',
            in_array($weatherCode, [45, 48]) => 'Fog',
            in_array($weatherCode, [51, 53, 55, 56, 57]) => 'Drizzle',
            in_array($weatherCode, [61, 63, 65, 66, 67]) => 'Rain',
            in_array($weatherCode, [71, 73, 75, 77]) => 'Snow',
            in_array($weatherCode, [80, 81, 82]) => 'Rain Showers',
            in_array($weatherCode, [95, 96, 99]) => 'Thunderstorm',
            default => 'Unknown',
        };
    }

    private function calculateWeatherRisk($temperature, $rainfall, $windSpeed, $weatherCode): int
    {
        $risk = 20;

        if ($rainfall >= 20 || $windSpeed >= 50) {
            $risk = 80;
        } elseif ($rainfall >= 10 || $windSpeed >= 30) {
            $risk = 60;
        } elseif ($temperature >= 35 || $temperature <= 0) {
            $risk = 40;
        }

        if (in_array($weatherCode, [95, 96, 99])) {
            $risk = 90;
        }

        return $risk;
    }
}