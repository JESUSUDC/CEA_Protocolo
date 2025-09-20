<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Users\Controller;

use App\Infrastructure\Entrypoint\Rest\Common\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\In\RefreshTokenUseCase;
use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Application\Users\Dto\Command\CreateUserCommand;
use App\Application\Users\Dto\Command\DeleteUserCommand;
use App\Application\Users\Dto\Query\GetUserByIdQuery;
use App\Application\Users\Dto\Query\ListUserQuery;
use App\Application\Users\Dto\Command\ChangePasswordCommand;
use App\Infrastructure\Entrypoint\Rest\Users\Mapper\UserHttpMapper;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends BaseController
{
    public function __construct(
        private CreateUserUseCase $createUser,
        private LoginUseCase $loginUseCase,
        private RefreshTokenUseCase $refreshTokenUseCase,
        private GetUserByIdUseCase $getByIdUseCase,
        private ListUsersUseCase $listUsersUseCase,
        private UpdateUserUseCase $updateUserUseCase,
        private DeleteUserUseCase $deleteUserUseCase,
        private ChangePasswordUseCase $changePasswordUseCase,
        private UserHttpMapper $mapper
    ) {}

    public function store(Request $request): JsonResponse
    {
        return $this->handleValidation(function () use ($request) {
            Log::info('Creating user', ['data' => $request->except('password')]);
            
            $validated = $this->validateRequest($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:8',
                'role' => 'sometimes|string|in:user,admin'
            ], 'UserCreation');

            $command = new CreateUserCommand(
                name: $validated['name'],
                role: $validated['role'] ?? 'user',
                email: $validated['email'],
                username: $validated['username'],
                password: $validated['password'],
                id: \Illuminate\Support\Str::uuid()->toString()
            );

            $userId = $this->createUser->execute($command);
            
            Log::info('User created successfully', ['user_id' => $userId]);
            return $this->successResponse(['id' => $userId], Response::HTTP_CREATED);
        }, 'UserCreation');
    }

    public function login(Request $request): JsonResponse
    {
        return $this->handleValidation(function () use ($request) {
            Log::info('Login attempt', ['username_or_email' => $request->input('username_or_email')]);
            
            $validated = $this->validateRequest($request, [
                'username_or_email' => 'required|string',
                'password' => 'required|string',
            ], 'UserLogin');

            $tokens = $this->loginUseCase->execute(
                $validated['username_or_email'], 
                $validated['password']
            );
            
            Log::info('Login successful', ['username_or_email' => $validated['username_or_email']]);
            return $this->successResponse($tokens);
        }, 'UserLogin');
    }

    public function refresh(Request $request): JsonResponse
    {
        return $this->handleValidation(function () use ($request) {
            Log::info('Refresh token attempt');
            
            $validated = $this->validateRequest($request, [
                'refresh_token' => 'required|string',
            ], 'TokenRefresh');

            $tokens = $this->refreshTokenUseCase->execute($validated['refresh_token']);
            
            Log::info('Refresh token successful');
            return $this->successResponse($tokens);
        }, 'TokenRefresh');
    }

    public function show(string $id): JsonResponse
    {
        try {
            $query = new GetUserByIdQuery($id);
            $userResp = $this->getByIdUseCase->execute($query);
            
            if ($userResp === null) {
                return $this->notFoundResponse('User not found');
            }
            
            return $this->successResponse($this->mapper->toHttp($userResp));
        } catch (\Throwable $e) {
            return $this->handleException($e, 'GetUserById');
        }
    }

    public function index(): JsonResponse
    {
        try {
            $limit = (int) request()->query('limit', 50);
            $offset = (int) request()->query('offset', 0);
            $query = new ListUserQuery($limit, $offset);
            
            $listResp = $this->listUsersUseCase->execute($query);
            
            return $this->successResponse(
                $this->mapper->toHttpList($listResp->items, $listResp->total)
            );
        } catch (\Throwable $e) {
            return $this->handleException($e, 'ListUsers');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->handleValidation(function () use ($request, $id) {
            Log::info('Updating user', ['user_id' => $id]);
            
            $validated = $this->validateRequest($request, [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'username' => 'sometimes|required|string|max:50|unique:users,username,' . $id,
                'role' => 'sometimes|string|in:user,admin'
            ], 'UserUpdate');

            $userResp = $this->getByIdUseCase->execute(new GetUserByIdQuery($id));
            if ($userResp === null) {
                return $this->notFoundResponse('User not found');
            }

            $command = $this->mapper->toUpdateCommand($validated, $id);
            $this->updateUserUseCase->execute($command);
            
            Log::info('User updated successfully', ['user_id' => $id]);
            return $this->successResponse([], Response::HTTP_NO_CONTENT);
        }, 'UserUpdate');
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $userResp = $this->getByIdUseCase->execute(new GetUserByIdQuery($id));
            if ($userResp === null) {
                return $this->notFoundResponse('User not found');
            }

            $this->deleteUserUseCase->execute(new DeleteUserCommand($id));
            
            Log::info('User deleted successfully', ['user_id' => $id]);
            return $this->successResponse([], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'DeleteUser');
        }
    }

    public function changePassword(Request $request, string $id): JsonResponse
    {
        return $this->handleValidation(function () use ($request, $id) {
            Log::info('Changing password', ['user_id' => $id]);
            
            $validated = $this->validateRequest($request, [
                'currentPassword' => 'required|string',
                'newPassword' => 'required|string|min:8',
            ], 'ChangePassword');

            $userResp = $this->getByIdUseCase->execute(new GetUserByIdQuery($id));
            if ($userResp === null) {
                return $this->notFoundResponse('User not found');
            }

            $command = new ChangePasswordCommand($id, $validated['currentPassword'], $validated['newPassword']);
            $this->changePasswordUseCase->execute($command);
            
            Log::info('Password changed successfully', ['user_id' => $id]);
            return $this->successResponse([], Response::HTTP_NO_CONTENT);
        }, 'ChangePassword');
    }
}