<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getCurrentWeather(Country $country): array
    {
        $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'current' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
            'timezone' => 'auto',
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Failed to fetch weather data',
                'data' => null,
            ];
        }

        $data = $response->json();

        $current = $data['current'] ?? [];

        $temperature = $current['temperature_2m'] ?? 0;
        $rainfall = $current['precipitation'] ?? 0;
        $windSpeed = $current['wind_speed_10m'] ?? 0;
        $weatherCode = $current['weather_code'] ?? null;
        $recordedAt = $current['time'] ?? now();

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
            'weather_risk' => $weatherRisk,
            'recorded_at' => $recordedAt,
        ]);

        return [
            'success' => true,
            'message' => 'Weather data retrieved successfully',
            'data' => [
                'temperature' => $temperature,
                'rainfall' => $rainfall,
                'wind_speed' => $windSpeed,
                'weather_code' => $weatherCode,
                'weather_condition' => $this->getWeatherCondition($weatherCode),
                'weather_risk' => $weatherRisk,
                'recorded_at' => $recordedAt,
            ],
        ];
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

    private function getWeatherCondition($weatherCode): string
    {
        return match ($weatherCode) {
            0 => 'Clear sky',
            1, 2, 3 => 'Cloudy',
            45, 48 => 'Fog',
            51, 53, 55 => 'Drizzle',
            61, 63, 65 => 'Rain',
            71, 73, 75 => 'Snow',
            80, 81, 82 => 'Rain showers',
            95, 96, 99 => 'Thunderstorm',
            default => 'Unknown',
        };
    }
}