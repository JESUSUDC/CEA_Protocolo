<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Cellphones\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Infrastructure\Entrypoint\Rest\Cellphones\Request\CreateCellphoneRequest;
use Infrastructure\Entrypoint\Rest\Cellphones\Request\UpdateCellphoneRequest;
use Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use Application\Cellphone\Port\In\ListCellphonesUseCase;
use Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use Application\Cellphone\Port\In\UpdateCellphoneUseCase;
use Application\Cellphone\Port\In\DeleteCellphoneUseCase;
use Application\Cellphone\Dto\Command\DeleteCellphoneCommand;
use Application\Cellphone\Dto\Query\GetCellphoneByIdQuery;
use Application\Cellphone\Dto\Query\ListCellphonesQuery;
use Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;

final class CellphoneController extends Controller
{
    public function __construct(
        private RegisterCellphoneUseCase $registerUseCase,
        private ListCellphonesUseCase $listUseCase,
        private GetCellphoneByIdUseCase $getByIdUseCase,
        private UpdateCellphoneUseCase $updateUseCase,
        private DeleteCellphoneUseCase $deleteUseCase,
        private CellphoneHttpMapper $mapper
    ) {}

    public function store(CreateCellphoneRequest $request): JsonResponse
    {
        try {
            $command = $this->mapper->toRegisterCommand($request->validated());
            $id = $this->registerUseCase->execute($command);
            return response()->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $query = new GetCellphoneByIdQuery($id);
            $cellResp = $this->getByIdUseCase->execute($query);
            if ($cellResp === null) {
                return response()->json(['message' => 'Cellphone not found'], 404);
            }
            return response()->json($this->mapper->toHttp($cellResp), 200);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $limit = (int) request()->query('limit', 50);
            $offset = (int) request()->query('offset', 0);
            $query = new ListCellphonesQuery($limit, $offset);
            $listResp = $this->listUseCase->execute($query);

            // DEBUG: inspecciona qué llega del UseCase
            dd($listResp);

            return response()->json(
                $this->mapper->toHttpList($listResp->items, $listResp->total),
                200
            );
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function update(UpdateCellphoneRequest $request, string $id): JsonResponse
    {
        try {
            $command = $this->mapper->toUpdateCommand($request->validated(), $id);
            $this->updateUseCase->execute($command);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->deleteUseCase->execute(new DeleteCellphoneCommand($id));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
