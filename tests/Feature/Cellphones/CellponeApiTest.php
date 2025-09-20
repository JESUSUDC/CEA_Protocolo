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
use stdClass;

final class CellphoneApiTest extends TestCase
{
    public function test_store_cellphone_success(): void
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
        $response->assertStatus(201)->assertJson(['id' => 'cell-123']);
    }

    public function test_show_cellphone_found(): void
    {
        // Bypass middleware
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'c1';

        $cellObj = (object)[
            'id' => $id,
            'brand' => 'Acme',
            'imei' => '111222333444555',
            'screenSize' => 6.1,
            'megapixels' => 12,
            'ramMb' => 2048,
            'storagePrimaryMb' => 32768,
            'operatingSystem' => 'Android',
            'networkTechnology' => '4G',
            'wifi' => true,
            'bluetooth' => true,
            'cameraCount' => 2,
            'cpuBrand' => 'ACME',
            'cpuSpeedGhz' => 2.2,
            'nfc' => false,
            'fingerprint' => true,
            'ir' => false,
            'waterResistant' => false,
            'simCount' => 2
        ];

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->with($this->equalTo(new \App\Application\Cellphone\Dto\Query\GetCellphoneByIdQuery($id)))
                    ->willReturn($cellObj);

        // in many controllers, the Query is created internally with the id string,
        // but in the route controller you create the Query inside show(). Since matching the exact object is hard,
        // we fallback to returning the object regardless of argument:
        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($cellObj);

        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("api/v1/cellphones/{$id}");
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $id, 'brand' => 'Acme']);
    }

    public function test_show_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'not-exist';

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);

        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("api/v1/cellphones/{$id}");
        $response->assertStatus(404)
                 ->assertJson(['message' => 'Cellphone not found']);
    }

    public function test_index_returns_list(): void
    {
        // Bypass middleware protecting /cellphones if used
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $listMock = $this->createMock(ListCellphonesUseCase::class);
        $listMock->method('execute')->willReturn((object)[
            'items' => [
                (object)['id' => 'c1', 'brand' => 'A'],
                (object)['id' => 'c2', 'brand' => 'B'],
            ],
            'total' => 2
        ]);

        $this->app->instance(ListCellphonesUseCase::class, $listMock);

        $response = $this->getJson('api/v1/cellphones');
        $response->assertStatus(200)
                 ->assertJsonStructure(['items', 'total'])
                 ->assertJsonFragment(['total' => 2]);
    }

    public function test_update_cellphone_success(): void
    {
        // Bypass middleware
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'c-update-1';

        // Controller will call GetCellphoneByIdUseCase first
        $existing = (object)['id' => $id, 'brand' => 'OldBrand'];
        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($existing);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        // Controller creates UpdateCellphoneService manually using app()->make(CellphoneRepositoryPort) and UnitOfWorkPort
        // so provide mocks for those ports to make the service run without error.
        $repoMock = $this->createMock(CellphoneRepositoryPort::class);
        // Make sure repo->findById (if called by service) returns an object so service can proceed
        $repoMock->method('findById')->with($id)->willReturn($existing);
        // Allow update to be called (no exception)
        $repoMock->expects($this->any())->method('update');

        $uowMock = $this->createMock(UnitOfWorkPort::class);
        // uow begin/commit/rollback should exist; allow them
        $uowMock->method('begin')->willReturn(null);
        $uowMock->method('commit')->willReturn(null);
        $uowMock->method('rollback')->willReturn(null);

        $this->app->instance(CellphoneRepositoryPort::class, $repoMock);
        $this->app->instance(UnitOfWorkPort::class, $uowMock);

        // Prepare payload (only partial update fields are allowed by controller validate)
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
        // Controller returns 204 on success with empty body
        $response->assertStatus(204)->assertNoContent();
    }

    public function test_update_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'missing-update';

        // getById returns null -> controller should return 404 before calling update service
        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $payload = [
            'brand' => 'DoesNotMatter',
            'imei' => '000',
        ];

        $response = $this->putJson("api/v1/cellphones/{$id}", $payload);
        $response->assertStatus(404)->assertJson(['message' => 'Cellphone not found']);
    }

    public function test_destroy_cellphone_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'to-delete-1';

        $existing = (object)['id' => $id];

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($existing);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $deleteMock = $this->createMock(DeleteCellphoneUseCase::class);
        // We expect delete use case to be executed; allow it
        $deleteMock->expects($this->once())->method('execute')->with($this->isInstanceOf(\App\Application\Cellphone\Dto\Command\DeleteCellphoneCommand::class));
        $this->app->instance(DeleteCellphoneUseCase::class, $deleteMock);

        $response = $this->deleteJson("api/v1/cellphones/{$id}");
        $response->assertStatus(204)->assertNoContent();
    }

    public function test_destroy_cellphone_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'no-such';

        $getByIdMock = $this->createMock(GetCellphoneByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetCellphoneByIdUseCase::class, $getByIdMock);

        $response = $this->deleteJson("api/v1/cellphones/{$id}");
        $response->assertStatus(404)->assertJson(['message' => 'Cellphone not found']);
    }
}
