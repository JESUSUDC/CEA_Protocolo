<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Infrastructure\Adapters\Database\Eloquent\Model\CellphoneModel;

class CellphoneSeeder extends Seeder
{
    public function run(): void
    {
        CellphoneModel::create([
            'id' => Str::uuid()->toString(),
            'brand' => 'Samsung',
            'imei' => '123456789012345',
            'screen_size' => 6.5,
            'megapixels' => 108,
            'ram_mb' => 8000,
            'storage_primary_mb' => 128000,
            'storage_secondary_mb' => 256000,
            'operating_system' => 'Android 13',
            'operator' => 'Claro',
            'network_technology' => '5G',
            'wifi' => true,
            'bluetooth' => true,
            'camera_count' => 4,
            'cpu_brand' => 'Exynos',
            'cpu_speed_ghz' => 2.9,
            'nfc' => true,
            'fingerprint' => true,
            'ir' => false,
            'water_resistant' => true,
            'sim_count' => 2,
        ]);

        CellphoneModel::create([
            'id' => Str::uuid()->toString(),
            'brand' => 'Apple',
            'imei' => '987654321098765',
            'screen_size' => 6.1,
            'megapixels' => 12,
            'ram_mb' => 4096,
            'storage_primary_mb' => 128000,
            'storage_secondary_mb' => null,
            'operating_system' => 'iOS 17',
            'operator' => 'Movistar',
            'network_technology' => '5G',
            'wifi' => true,
            'bluetooth' => true,
            'camera_count' => 2,
            'cpu_brand' => 'Apple A17',
            'cpu_speed_ghz' => 3.2,
            'nfc' => true,
            'fingerprint' => false,
            'ir' => false,
            'water_resistant' => true,
            'sim_count' => 1,
        ]);
    }
}
