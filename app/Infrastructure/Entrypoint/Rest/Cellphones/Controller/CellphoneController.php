<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Cellphones\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Infrastructure\Entrypoint\Rest\Cellphones\Request\CreateCellphoneRequest;
use Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use Application\Port\In\RegisterCellphoneUseCase;

final class CellphoneController extends Controller
{
    public function __construct(
        private RegisterCellphoneUseCase $registerUseCase,
        private CellphoneHttpMapper $mapper
    ) {}

    public function store(CreateCellphoneRequest $request): JsonResponse
    {
        $dto = $request->validated();
        try {
            $command = $this->mapper->toRegisterCommand($dto);
            $cellphoneId = $this->registerUseCase->execute($command);
            return response()->json(['id' => $cellphoneId], 201);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            // Use case to get cellphone by id
            $cell = $this->registerUseCase->findById($id); // adapt to your API
            if (!$cell) {
                return response()->json(['message' => 'Not found'], 404);
            }
            return response()->json($this->mapper->toHttp($cell));
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
