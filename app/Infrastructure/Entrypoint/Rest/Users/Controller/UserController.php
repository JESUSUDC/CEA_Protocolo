<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Users\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Infrastructure\Entrypoint\Rest\Users\Request\CreateUserHttpRequest;
use App\Infrastructure\Entrypoint\Rest\Users\Request\LoginUserRequest;
use App\Infrastructure\Entrypoint\Rest\Users\Request\UpdateUserHttpRequest;
use App\Infrastructure\Entrypoint\Rest\Users\Request\ChangePasswordRequest;
use App\Infrastructure\Entrypoint\Rest\Users\Mapper\UserHttpMapper;
use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Port\In\LogoutUseCase;
use App\Application\Users\Dto\Command\DeleteUserCommand;
use App\Application\Users\Dto\Query\GetUserByIdQuery;
use App\Application\Users\Dto\Query\ListUserQuery;
use App\Application\Users\Dto\Command\ChangePasswordCommand;
use App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;

final class UserController extends Controller
{
    public function __construct(
        private CreateUserUseCase $createUser,
        private LoginUseCase $loginUseCase,
        private GetUserByIdUseCase $getByIdUseCase,
        private ListUsersUseCase $listUsersUseCase,
        private UpdateUserUseCase $updateUserUseCase,
        private DeleteUserUseCase $deleteUserUseCase,
        private ChangePasswordUseCase $changePasswordUseCase,
        private LogoutUseCase $logoutUseCase,
        private UserHttpMapper $mapper
    ) {}

    public function store(CreateUserHttpRequest $request): JsonResponse
    {
        try {
            $command = $this->mapper->toCreateCommand($request->validated());
            $id = $this->createUser->execute($command);
            return response()->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $token = $this->loginUseCase->execute($data['username_or_email'], $data['password']);
            return response()->json(['token' => $token], 200);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $query = new GetUserByIdQuery($id);
            $userResp = $this->getByIdUseCase->execute($query);
            if ($userResp === null) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json($this->mapper->toHttp($userResp), 200);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function index(): JsonResponse
    {
        try {
            // request query params handled here (no form request needed)
            $limit = (int) request()->query('limit', 50);
            $offset = (int) request()->query('offset', 0);
            $query = new ListUserQuery($limit, $offset);
            $listResp = $this->listUsersUseCase->execute($query);
            return response()->json($this->mapper->toHttpList($listResp->items, $listResp->total), 200);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function update(UpdateUserHttpRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $command = $this->mapper->toUpdateCommand($validated, $id);
            $this->updateUserUseCase->execute($command);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->deleteUserUseCase->execute(new DeleteUserCommand($id));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function changePassword(ChangePasswordRequest $request, string $id): JsonResponse
    {
        try {
            $payload = $request->validated();
            $command = new ChangePasswordCommand($id, $payload['currentPassword'], $payload['newPassword']);
            $this->changePasswordUseCase->execute($command);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function logout(string $id): JsonResponse
    {
        try {
            $this->logoutUseCase->execute($id);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
