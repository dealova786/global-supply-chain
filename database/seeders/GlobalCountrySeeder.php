<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class GlobalCountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['Indonesia', 'Republic of Indonesia', 'ID', 'IDN', 'Jakarta', 'Asia', 'South-Eastern Asia', -6.2000, 106.8167, 'IDR', 'Indonesian Rupiah', 'Indonesian', 275000000],
            ['China', 'People\'s Republic of China', 'CN', 'CHN', 'Beijing', 'Asia', 'Eastern Asia', 39.9042, 116.4074, 'CNY', 'Chinese Yuan', 'Chinese', 1411000000],
            ['Japan', 'Japan', 'JP', 'JPN', 'Tokyo', 'Asia', 'Eastern Asia', 35.6762, 139.6503, 'JPY', 'Japanese Yen', 'Japanese', 125000000],
            ['South Korea', 'Republic of Korea', 'KR', 'KOR', 'Seoul', 'Asia', 'Eastern Asia', 37.5665, 126.9780, 'KRW', 'South Korean Won', 'Korean', 51700000],
            ['Singapore', 'Republic of Singapore', 'SG', 'SGP', 'Singapore', 'Asia', 'South-Eastern Asia', 1.3521, 103.8198, 'SGD', 'Singapore Dollar', 'English', 5900000],
            ['Malaysia', 'Malaysia', 'MY', 'MYS', 'Kuala Lumpur', 'Asia', 'South-Eastern Asia', 3.1390, 101.6869, 'MYR', 'Malaysian Ringgit', 'Malay', 34000000],
            ['Thailand', 'Kingdom of Thailand', 'TH', 'THA', 'Bangkok', 'Asia', 'South-Eastern Asia', 13.7563, 100.5018, 'THB', 'Thai Baht', 'Thai', 71600000],
            ['Vietnam', 'Socialist Republic of Vietnam', 'VN', 'VNM', 'Hanoi', 'Asia', 'South-Eastern Asia', 21.0285, 105.8542, 'VND', 'Vietnamese Dong', 'Vietnamese', 98000000],
            ['India', 'Republic of India', 'IN', 'IND', 'New Delhi', 'Asia', 'Southern Asia', 28.6139, 77.2090, 'INR', 'Indian Rupee', 'Hindi', 1428000000],
            ['Australia', 'Commonwealth of Australia', 'AU', 'AUS', 'Canberra', 'Oceania', 'Australia and New Zealand', -35.2809, 149.1300, 'AUD', 'Australian Dollar', 'English', 26000000],

            ['Germany', 'Federal Republic of Germany', 'DE', 'DEU', 'Berlin', 'Europe', 'Western Europe', 52.5200, 13.4050, 'EUR', 'Euro', 'German', 84000000],
            ['Netherlands', 'Kingdom of the Netherlands', 'NL', 'NLD', 'Amsterdam', 'Europe', 'Western Europe', 52.3676, 4.9041, 'EUR', 'Euro', 'Dutch', 17700000],
            ['France', 'French Republic', 'FR', 'FRA', 'Paris', 'Europe', 'Western Europe', 48.8566, 2.3522, 'EUR', 'Euro', 'French', 68000000],
            ['United Kingdom', 'United Kingdom of Great Britain and Northern Ireland', 'GB', 'GBR', 'London', 'Europe', 'Northern Europe', 51.5072, -0.1276, 'GBP', 'Pound Sterling', 'English', 67000000],
            ['Italy', 'Italian Republic', 'IT', 'ITA', 'Rome', 'Europe', 'Southern Europe', 41.9028, 12.4964, 'EUR', 'Euro', 'Italian', 59000000],
            ['Spain', 'Kingdom of Spain', 'ES', 'ESP', 'Madrid', 'Europe', 'Southern Europe', 40.4168, -3.7038, 'EUR', 'Euro', 'Spanish', 48000000],
            ['Belgium', 'Kingdom of Belgium', 'BE', 'BEL', 'Brussels', 'Europe', 'Western Europe', 50.8503, 4.3517, 'EUR', 'Euro', 'Dutch/French', 11600000],

            ['United States', 'United States of America', 'US', 'USA', 'Washington, D.C.', 'Americas', 'Northern America', 38.9072, -77.0369, 'USD', 'United States Dollar', 'English', 333000000],
            ['Canada', 'Canada', 'CA', 'CAN', 'Ottawa', 'Americas', 'Northern America', 45.4215, -75.6972, 'CAD', 'Canadian Dollar', 'English/French', 40000000],
            ['Mexico', 'United Mexican States', 'MX', 'MEX', 'Mexico City', 'Americas', 'North America', 19.4326, -99.1332, 'MXN', 'Mexican Peso', 'Spanish', 128000000],
            ['Brazil', 'Federative Republic of Brazil', 'BR', 'BRA', 'Brasília', 'Americas', 'South America', -15.7939, -47.8828, 'BRL', 'Brazilian Real', 'Portuguese', 203000000],

            ['United Arab Emirates', 'United Arab Emirates', 'AE', 'ARE', 'Abu Dhabi', 'Asia', 'Western Asia', 24.4539, 54.3773, 'AED', 'UAE Dirham', 'Arabic', 9800000],
            ['Saudi Arabia', 'Kingdom of Saudi Arabia', 'SA', 'SAU', 'Riyadh', 'Asia', 'Western Asia', 24.7136, 46.6753, 'SAR', 'Saudi Riyal', 'Arabic', 36000000],
            ['Turkey', 'Republic of Türkiye', 'TR', 'TUR', 'Ankara', 'Asia', 'Western Asia', 39.9334, 32.8597, 'TRY', 'Turkish Lira', 'Turkish', 85000000],
            ['South Africa', 'Republic of South Africa', 'ZA', 'ZAF', 'Pretoria', 'Africa', 'Southern Africa', -25.7479, 28.2293, 'ZAR', 'South African Rand', 'English', 60000000],
        ];

        foreach ($countries as $country) {
            [
                $name,
                $officialName,
                $cca2,
                $cca3,
                $capital,
                $region,
                $subregion,
                $latitude,
                $longitude,
                $currencyCode,
                $currencyName,
                $language,
                $population
            ] = $country;

            Country::updateOrCreate(
                ['cca2' => $cca2],
                [
                    'name' => $name,
                    'official_name' => $officialName,
                    'cca3' => $cca3,
                    'capital' => $capital,
                    'region' => $region,
                    'subregion' => $subregion,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'language' => $language,
                    'population' => $population,
                    'flag_url' => 'https://flagcdn.com/w320/' . strtolower($cca2) . '.png',
                ]
            );
        }
    }
}