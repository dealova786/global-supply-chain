<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Indonesia',
                'official_name' => 'Republic of Indonesia',
                'cca2' => 'ID',
                'cca3' => 'IDN',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => -6.2000000,
                'longitude' => 106.8166667,
                'currency_code' => 'IDR',
                'currency_name' => 'Indonesian Rupiah',
                'language' => 'Indonesian',
                'population' => 277000000,
                'flag_url' => 'https://flagcdn.com/w320/id.png',
            ],
            [
                'name' => 'China',
                'official_name' => 'People\'s Republic of China',
                'cca2' => 'CN',
                'cca3' => 'CHN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'latitude' => 39.9042000,
                'longitude' => 116.4074000,
                'currency_code' => 'CNY',
                'currency_name' => 'Chinese Yuan',
                'language' => 'Chinese',
                'population' => 1412000000,
                'flag_url' => 'https://flagcdn.com/w320/cn.png',
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'cca2' => 'DE',
                'cca3' => 'DEU',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'latitude' => 52.5200000,
                'longitude' => 13.4050000,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'language' => 'German',
                'population' => 84000000,
                'flag_url' => 'https://flagcdn.com/w320/de.png',
            ],
            [
                'name' => 'Australia',
                'official_name' => 'Commonwealth of Australia',
                'cca2' => 'AU',
                'cca3' => 'AUS',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'latitude' => -35.2809000,
                'longitude' => 149.1300000,
                'currency_code' => 'AUD',
                'currency_name' => 'Australian Dollar',
                'language' => 'English',
                'population' => 26000000,
                'flag_url' => 'https://flagcdn.com/w320/au.png',
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['cca3' => $country['cca3']],
                $country
            );
        }
    }
}