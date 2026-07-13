<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            [
                'country' => 'Indonesia',
                'port_name' => 'Port of Tanjung Priok',
                'country_name' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => -6.1045,
                'longitude' => 106.8808,
                'region' => 'Asia',
                'harbor_size' => 'Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'Indonesia',
                'port_name' => 'Port of Tanjung Perak',
                'country_name' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => -7.2058,
                'longitude' => 112.7342,
                'region' => 'Asia',
                'harbor_size' => 'Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'China',
                'port_name' => 'Port of Shanghai',
                'country_name' => 'China',
                'country_code' => 'CN',
                'latitude' => 31.2304,
                'longitude' => 121.4737,
                'region' => 'Asia',
                'harbor_size' => 'Very Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'China',
                'port_name' => 'Port of Shenzhen',
                'country_name' => 'China',
                'country_code' => 'CN',
                'latitude' => 22.5431,
                'longitude' => 114.0579,
                'region' => 'Asia',
                'harbor_size' => 'Very Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'Germany',
                'port_name' => 'Port of Hamburg',
                'country_name' => 'Germany',
                'country_code' => 'DE',
                'latitude' => 53.5511,
                'longitude' => 9.9937,
                'region' => 'Europe',
                'harbor_size' => 'Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'Germany',
                'port_name' => 'Port of Bremen',
                'country_name' => 'Germany',
                'country_code' => 'DE',
                'latitude' => 53.0793,
                'longitude' => 8.8017,
                'region' => 'Europe',
                'harbor_size' => 'Medium',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'Australia',
                'port_name' => 'Port Botany',
                'country_name' => 'Australia',
                'country_code' => 'AU',
                'latitude' => -33.9667,
                'longitude' => 151.2250,
                'region' => 'Oceania',
                'harbor_size' => 'Large',
                'port_type' => 'Seaport',
            ],
            [
                'country' => 'Australia',
                'port_name' => 'Port of Melbourne',
                'country_name' => 'Australia',
                'country_code' => 'AU',
                'latitude' => -37.8136,
                'longitude' => 144.9631,
                'region' => 'Oceania',
                'harbor_size' => 'Large',
                'port_type' => 'Seaport',
            ],
        ];

        foreach ($ports as $data) {
            $country = Country::where('name', $data['country'])->first();

            Port::updateOrCreate(
                [
                    'port_name' => $data['port_name'],
                    'country_code' => $data['country_code'],
                ],
                [
                    'country_id' => $country?->id,
                    'port_name' => $data['port_name'],
                    'country_name' => $data['country_name'],
                    'country_code' => $data['country_code'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'region' => $data['region'],
                    'harbor_size' => $data['harbor_size'],
                    'port_type' => $data['port_type'],
                ]
            );
        }
    }
}