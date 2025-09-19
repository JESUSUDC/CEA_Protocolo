<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class CellphoneTest extends TestCase
{
    public function test_register_cellphone(): void
    {
        $response = $this->postJson('/api/v1/cellphones', [
            'brand' => 'Samsung',
            'imei' => '123456789012345',
            'screen_size' => 6.5,
            'megapixels' => 48,
            'ram_mb' => 8192,
            'storage_primary_mb' => 128000,
            'network_technology' => '5G',
            'camera_count' => 4,
            'cpu_brand' => 'Snapdragon',
            'cpu_speed_ghz' => 2.8,
            'sim_count' => 2
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id']);
    }

    public function test_list_cellphones(): void
    {
        $response = $this->getJson('/api/v1/cellphones');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id','brand','imei']]);
    }
}
