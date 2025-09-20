<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Controller;

use App\Infrastructure\Entrypoint\Rest\Common\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use App\Application\Cellphone\Port\In\DeleteCellphoneUseCase;
use App\Application\Cellphone\Dto\Command\DeleteCellphoneCommand;
use App\Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use App\Application\Cellphone\Dto\Query\ListCellphonesQuery;
use App\Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use Symfony\Component\HttpFoundation\Response;

final class CellphoneController extends BaseController
{
    public function __construct(
        private RegisterCellphoneUseCase $registerUseCase,
        private ListCellphonesUseCase $listUseCase,
        private GetCellphoneByIdUseCase $getByIdUseCase,
        private DeleteCellphoneUseCase $deleteUseCase,
        private CellphoneHttpMapper $mapper
    ) {}

    public function store(Request $request): JsonResponse
    {
        return $this->handleValidation(function () use ($request) {
            $validated = $this->validateRequest($request, [
                'brand' => 'required|string|max:255',
                'imei' => 'required|string|unique:cellphones,imei',
                'screen_size' => 'required|numeric|min:0',
                'megapixels' => 'required|numeric|min:0',
                'ram_mb' => 'required|integer|min:0',
                'storage_primary_mb' => 'required|integer|min:0',
                'storage_secondary_mb' => 'nullable|integer|min:0',
                'operating_system' => 'required|string|max:100',
                'operator' => 'nullable|string|max:100',
                'network_technology' => 'required|string|max:100',
                'wifi' => 'required|boolean',
                'bluetooth' => 'required|boolean',
                'camera_count' => 'required|integer|min:0',
                'cpu_brand' => 'required|string|max:100',
                'cpu_speed_ghz' => 'required|numeric|min:0',
                'nfc' => 'required|boolean',
                'fingerprint' => 'required|boolean',
                'ir' => 'required|boolean',
                'water_resistant' => 'required|boolean',
                'sim_count' => 'required|integer|min:0',
            ], 'CellphoneCreation');

            $command = $this->mapper->toRegisterCommand($validated);
            $id = $this->registerUseCase->execute($command);
            
            Log::info('Cellphone created successfully', ['cellphone_id' => $id]);
            return $this->successResponse(['id' => $id], Response::HTTP_CREATED);
        }, 'CellphoneCreation');
    }

    public function show(string $id): JsonResponse
    {
        try {
            $cellResp = $this->getByIdUseCase->execute(new GetCellphoneByIdQuery($id));
            
            if ($cellResp === null) {
                return $this->notFoundResponse('Cellphone not found');
            }
            
            return $this->successResponse($this->mapper->toHttp($cellResp));
        } catch (\Throwable $e) {
            return $this->handleException($e, 'GetCellphoneById');
        }
    }

    public function index(): JsonResponse
    {
        try {
            $limit = (int) request()->query('limit', 50);
            $offset = (int) request()->query('offset', 0);
            $query = new ListCellphonesQuery($limit, $offset);
            
            $listResp = $this->listUseCase->execute($query);
            
            // Usar el mapper para convertir CellphoneListResponse a formato HTTP
            $httpResponse = $this->mapper->toHttpList($listResp);
            
            return $this->successResponse($httpResponse);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'ListCellphones');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->handleValidation(function () use ($request, $id) {
            Log::info('Updating cellphone', ['cellphone_id' => $id]);
            
            $validated = $this->validateRequest($request, [
                'brand' => 'sometimes|required|string|max:255',
                'imei' => 'sometimes|required|string|unique:cellphones,imei,' . $id,
                'screen_size' => 'sometimes|required|numeric|min:0',
                'megapixels' => 'sometimes|required|numeric|min:0',
                'ram_mb' => 'sometimes|required|integer|min:0',
                'storage_primary_mb' => 'sometimes|required|integer|min:0',
                'storage_secondary_mb' => 'sometimes|nullable|integer|min:0',
                'operating_system' => 'sometimes|required|string|max:100',
                'operator' => 'sometimes|nullable|string|max:100',
                'network_technology' => 'sometimes|required|string|max:100',
                'wifi' => 'sometimes|required|boolean',
                'bluetooth' => 'sometimes|required|boolean',
                'camera_count' => 'sometimes|required|integer|min:0',
                'cpu_brand' => 'sometimes|required|string|max:100',
                'cpu_speed_ghz' => 'sometimes|required|numeric|min:0',
                'nfc' => 'sometimes|required|boolean',
                'fingerprint' => 'sometimes|required|boolean',
                'ir' => 'sometimes|required|boolean',
                'water_resistant' => 'sometimes|required|boolean',
                'sim_count' => 'sometimes|required|integer|min:0',
            ], 'CellphoneUpdate');

            $cellResp = $this->getByIdUseCase->execute(new GetCellphoneByIdQuery($id));
            if ($cellResp === null) {
                return $this->notFoundResponse('Cellphone not found');
            }

            // Lógica de actualización manual
            $command = $this->mapper->toUpdateCommand($validated, $id);
            $repo = app()->make(\App\Application\Cellphone\Port\Out\CellphoneRepositoryPort::class);
            $uow = app()->make(\App\Application\Users\Port\Out\UnitOfWorkPort::class);
            
            $updateService = new \App\Application\Cellphone\Service\UpdateCellphoneService($repo, $uow);
            $updateService->execute($command);

            Log::info('Cellphone updated successfully', ['cellphone_id' => $id]);
            return $this->successResponse([], Response::HTTP_NO_CONTENT);
        }, 'CellphoneUpdate');
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $cellResp = $this->getByIdUseCase->execute(new GetCellphoneByIdQuery($id));
            if ($cellResp === null) {
                return $this->notFoundResponse('Cellphone not found');
            }

            $this->deleteUseCase->execute(new DeleteCellphoneCommand($id));
            
            Log::info('Cellphone deleted successfully', ['cellphone_id' => $id]);
            return $this->successResponse([], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'DeleteCellphone');
        }
    }
}