<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Infrastructure\Entrypoint\Rest\Users\Request\CreateUserHttpRequest;
use Infrastructure\Entrypoint\Rest\Users\Mapper\UserHttpMapper;
use Application\Port\In\RegisterUserUseCase;
use Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;

final class UserController extends Controller
{
    public function __construct(
        private RegisterUserUseCase $registerUseCase,
        private UserHttpMapper $mapper
    ) {}

    public function store(CreateUserHttpRequest $request): JsonResponse
    {
        try {
            $command = $this->mapper->toRegisterCommand($request->validated());
            $id = $this->registerUseCase->execute($command);
            return response()->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    // add login, logout, list, update etc. similarly
}
