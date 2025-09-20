<?php
declare(strict_types=1);

namespace Tests\Feature\Cellphones;

use Tests\TestCase;
use App\Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use App\Application\Cellphone\Port\In\DeleteCellphoneUseCase;
use App\Application\Cellphone\Port\Out\CellphoneRepositoryPort;
use App\Application\Users\Port\Out\UnitOfWorkPort;
use App\Infrastructure\Entrypoint\Rest\Middleware\JwtAuthMiddleware;
use App\Application\Cellphone\Dto\Response\CellphoneResponse;
use App\Application\Cellphone\Dto\Response\CellphoneListResponse;
use Symfony\Component\HttpFoundation\Response;

final class CellphoneApiTest extends TestCase
{
    /*public function test_store_cellphone_success(): void
    {
        $registerMock = $this->createMock(RegisterCellphoneUseCase::class);
        $registerMock->method('execute')->willReturn('cell-123');

        $this->app->instance(RegisterCellphoneUseCase::class, $registerMock);

        $payload = [
            'brand' => 'Acme',
            'imei' => '123456789012345',
            'screen_size' => 6.1,
            'megapixels' => 12,
            'ram_mb' => 4096,
            'storage_primary_mb' => 65536,
            'operating_system' => 'Android',
            'network_technology' => '4G',
            'wifi' => true,
            'bluetooth' => true,
            'camera_count' => 2,
            'cpu_brand' => 'ACME',
            'cpu_speed_ghz' => 2.4,
            'nfc' => false,
            'fingerprint' => true,
            'ir' => false,
            'water_resistant' => false,
            'sim_count' => 2
        ];

        $response = $this->postJson('api/v1/cellphones', $payload);
        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJson(['id' => 'cell-123']);
    }

    public function test_store_cellphone_validation_error(): void
    {
        $payload = [
            'brand' => '', // Campo requerido vacío
            'imei' => '123',
            // Faltan campos requeridos
        ];

        $response = $this->postJson('api/v1/cellphones', $payload);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance',
                     'invalid_params'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Invalid Input',
                     'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                 ]);
    }

    public function test_show_cellphone_found(): void
    {
        // Bypass middleware
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'c1';

        $cellResponse = new CellphoneResponse(
            id: $id,
            brand: 'Acme',
            imei: '111222333444555',
            screenSize: 6.1,
            megapixels: 12.0,
            ramMb: 2048,
            storagePrimaryMb: 32768,
            storageSecondaryMb: null,
            operatingSystem: 'Android',
            operator: null,
            networkTechnology: '4G',
            wifi: true,
            bluetooth: true,
            cameraCount: 2,
            cpuBrand: 'ACME',
            cpuSpeedGhz: 2.2,
            nfc: false,
            fingerprint: true,
            ir: false,
            waterResistant: false,
            simCount: 2
        );

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($cellResponse);

        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("api/v1/cellphones/{$id}");
        
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure([
                     'id', 'brand', 'imei', 'screen_size', 'megapixels',
                     'ram_mb', 'storage_primary_mb', 'storage_secondary_mb',
                     'operating_system', 'operator', 'network_technology',
                     'wifi', 'bluetooth', 'camera_count', 'cpu_brand',
                     'cpu_speed_ghz', 'nfc', 'fingerprint', 'ir',
                     'water_resistant', 'sim_count'
                 ])
                 ->assertJsonFragment([
                     'id' => $id,
                     'brand' => 'Acme',
                     'imei' => '111222333444555'
                 ]);
    }*/

    public function test_show_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'not-exist';

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);

        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("api/v1/cellphones/{$id}");
        
        $response->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Not Found',
                     'status' => Response::HTTP_NOT_FOUND,
                     'detail' => 'Cellphone not found'
                 ]);
    }

    public function test_index_returns_list(): void
    {
        // Bypass middleware protecting /cellphones if used
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $cellphone1 = new CellphoneResponse(
            id: 'c1',
            brand: 'Brand A',
            imei: '111111111111111',
            screenSize: 6.1,
            megapixels: 12.0,
            ramMb: 4096,
            storagePrimaryMb: 65536,
            storageSecondaryMb: null,
            operatingSystem: 'Android',
            operator: null,
            networkTechnology: '4G',
            wifi: true,
            bluetooth: true,
            cameraCount: 2,
            cpuBrand: 'CPU A',
            cpuSpeedGhz: 2.2,
            nfc: false,
            fingerprint: true,
            ir: false,
            waterResistant: false,
            simCount: 2
        );

        $cellphone2 = new CellphoneResponse(
            id: 'c2',
            brand: 'Brand B',
            imei: '222222222222222',
            screenSize: 6.5,
            megapixels: 48.0,
            ramMb: 8192,
            storagePrimaryMb: 128000,
            storageSecondaryMb: 512000,
            operatingSystem: 'iOS',
            operator: 'Movistar',
            networkTechnology: '5G',
            wifi: true,
            bluetooth: true,
            cameraCount: 3,
            cpuBrand: 'CPU B',
            cpuSpeedGhz: 3.0,
            nfc: true,
            fingerprint: true,
            ir: true,
            waterResistant: true,
            simCount: 1
        );

        $listResponse = new CellphoneListResponse([$cellphone1, $cellphone2], 2);

        $listMock = $this->createMock(ListCellphonesUseCase::class);
        $listMock->method('execute')->willReturn($listResponse);

        $this->app->instance(ListCellphonesUseCase::class, $listMock);

        $response = $this->getJson('api/v1/cellphones');
        
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure([
                     'items' => [
                         '*' => [
                             'id', 'brand', 'imei', 'screen_size', 'megapixels',
                             'ram_mb', 'storage_primary_mb', 'storage_secondary_mb',
                             'operating_system', 'operator', 'network_technology',
                             'wifi', 'bluetooth', 'camera_count', 'cpu_brand',
                             'cpu_speed_ghz', 'nfc', 'fingerprint', 'ir',
                             'water_resistant', 'sim_count'
                         ]
                     ],
                     'total'
                 ])
                 ->assertJsonFragment(['total' => 2])
                 ->assertJsonCount(2, 'items');
    }

    /*public function test_update_cellphone_success(): void
    {
        // Bypass middleware
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'c-update-1';

        // Controller will call GetCellphoneByIdUseCase first
        $existing = new CellphoneResponse(
            id: $id,
            brand: 'OldBrand',
            imei: '111111111111111',
            screenSize: 6.1,
            megapixels: 12.0,
            ramMb: 4096,
            storagePrimaryMb: 65536,
            storageSecondaryMb: null,
            operatingSystem: 'Android',
            operator: null,
            networkTechnology: '4G',
            wifi: true,
            bluetooth: true,
            cameraCount: 2,
            cpuBrand: 'OldCPU',
            cpuSpeedGhz: 2.2,
            nfc: false,
            fingerprint: true,
            ir: false,
            waterResistant: false,
            simCount: 2
        );

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($existing);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        // Controller creates UpdateCellphoneService manually
        $repoMock = $this->createMock(CellphoneRepositoryPort::class);
        $repoMock->method('findById')->with($id)->willReturn($existing);
        $repoMock->expects($this->any())->method('update');

        $uowMock = $this->createMock(UnitOfWorkPort::class);
        $uowMock->method('begin')->willReturn(null);
        $uowMock->method('commit')->willReturn(null);
        $uowMock->method('rollback')->willReturn(null);

        $this->app->instance(CellphoneRepositoryPort::class, $repoMock);
        $this->app->instance(UnitOfWorkPort::class, $uowMock);

        $payload = [
            'brand' => 'NewBrand',
            'imei' => '999888777666555',
            'screen_size' => 6.5,
            'megapixels' => 48,
            'ram_mb' => 8192,
            'storage_primary_mb' => 128000,
            'operating_system' => 'Android',
            'network_technology' => '5G',
            'wifi' => true,
            'bluetooth' => true,
            'camera_count' => 3,
            'cpu_brand' => 'ACME',
            'cpu_speed_ghz' => 3.0,
            'nfc' => true,
            'fingerprint' => true,
            'ir' => false,
            'water_resistant' => true,
            'sim_count' => 2
        ];

        $response = $this->putJson("api/v1/cellphones/{$id}", $payload);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_update_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'missing-update';

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $payload = [
            'brand' => 'DoesNotMatter',
            'imei' => '000',
        ];

        $response = $this->putJson("api/v1/cellphones/{$id}", $payload);
        
        $response->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Not Found',
                     'status' => Response::HTTP_NOT_FOUND,
                     'detail' => 'Cellphone not found'
                 ]);
    }

    public function test_update_cellphone_validation_error(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'c-update-1';
        $existing = new CellphoneResponse(
            id: $id,
            brand: 'OldBrand',
            imei: '111111111111111',
            screenSize: 6.1,
            megapixels: 12.0,
            ramMb: 4096,
            storagePrimaryMb: 65536,
            storageSecondaryMb: null,
            operatingSystem: 'Android',
            operator: null,
            networkTechnology: '4G',
            wifi: true,
            bluetooth: true,
            cameraCount: 2,
            cpuBrand: 'OldCPU',
            cpuSpeedGhz: 2.2,
            nfc: false,
            fingerprint: true,
            ir: false,
            waterResistant: false,
            simCount: 2
        );

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($existing);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $payload = [
            'brand' => '', // Campo requerido vacío
            'imei' => 'invalid', // IMEI muy corto
        ];

        $response = $this->putJson("api/v1/cellphones/{$id}", $payload);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance',
                     'invalid_params'
                 ]);
    }*/

    public function test_destroy_cellphone_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'to-delete-1';

        $existing = new CellphoneResponse(
            id: $id,
            brand: 'BrandToDelete',
            imei: '111111111111111',
            screenSize: 6.1,
            megapixels: 12.0,
            ramMb: 4096,
            storagePrimaryMb: 65536,
            storageSecondaryMb: null,
            operatingSystem: 'Android',
            operator: null,
            networkTechnology: '4G',
            wifi: true,
            bluetooth: true,
            cameraCount: 2,
            cpuBrand: 'CPU',
            cpuSpeedGhz: 2.2,
            nfc: false,
            fingerprint: true,
            ir: false,
            waterResistant: false,
            simCount: 2
        );

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($existing);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $deleteMock = $this->createMock(DeleteCellphoneUseCase::class);
        $deleteMock->expects($this->once())->method('execute')->with($this->isInstanceOf(\App\Application\Cellphone\Dto\Command\DeleteCellphoneCommand::class));
        $this->app->instance(DeleteCellphoneUseCase::class, $deleteMock);

        $response = $this->deleteJson("api/v1/cellphones/{$id}");
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_destroy_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'no-such';

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->deleteJson("api/v1/cellphones/{$id}");
        
        $response->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Not Found',
                     'status' => Response::HTTP_NOT_FOUND,
                     'detail' => 'Cellphone not found'
                 ]);
    }

    /*public function test_protected_routes_require_authentication(): void
    {
        // No bypass middleware - debería fallar sin token
        $response = $this->getJson('api/v1/cellphones');
        
        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Unauthorized',
                     'status' => Response::HTTP_UNAUTHORIZED
                 ]);
    }*/
}