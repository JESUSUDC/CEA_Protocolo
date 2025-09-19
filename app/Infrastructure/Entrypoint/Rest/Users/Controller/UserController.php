<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Infrastructure\Entrypoint\Rest\Users\Request\CreateUserHttpRequest;
use Illuminate\Http\Request;
use Infrastructure\Entrypoint\Rest\Users\Mapper\UserHttpMapper;
use Application\Users\Port\In\CreateUserUseCase;
use Application\Users\Port\In\LoginUseCase;
use Application\Users\Port\In\GetUserByIdUseCase;
use Application\Users\Port\In\ListUsersUseCase;
use Application\Users\Port\In\UpdateUserUseCase;
use Application\Users\Port\In\DeleteUserUseCase;
use Application\Users\Port\In\ChangePasswordUseCase;
use Application\Users\Port\In\LogoutUseCase;
use Application\Users\Dto\Command\ChangePasswordCommand;
use Application\Users\Dto\Command\CreateUserCommand;
use Application\Users\Dto\Query\GetUserByIdQuery;
use Application\Users\Dto\Query\ListUserQuery;
use Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;

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

    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'username_or_email' => 'required|string',
                'password' => 'required|string'
            ]);

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

    public function index(Request $request): JsonResponse
    {
        try {
            $limit = (int) ($request->query('limit', 50));
            $offset = (int) ($request->query('offset', 0));
            $query = new ListUserQuery($limit, $offset);
            $listResp = $this->listUsersUseCase->execute($query);
            return response()->json($this->mapper->toHttpList($listResp->items, $listResp->total), 200);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|min:3',
                'role' => 'sometimes|string|in:admin,user,support',
                'email' => 'sometimes|email',
                'username' => 'sometimes|string|min:3',
            ]);
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
            $this->deleteUserUseCase->execute(new \Application\Users\Dto\Command\DeleteUserCommand($id));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function changePassword(Request $request, string $id): JsonResponse
    {
        try {
            $payload = $request->validate([
                'currentPassword' => 'required|string',
                'newPassword' => 'required|string|min:8'
            ]);
            $command = new ChangePasswordCommand($id, $payload['currentPassword'], $payload['newPassword']);
            $this->changePasswordUseCase->execute($command);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function logout(Request $request, string $id): JsonResponse
    {
        try {
            $this->logoutUseCase->execute($id);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
