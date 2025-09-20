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
use App\Application\Users\Port\In\RefreshTokenUseCase;
use App\Domain\Users\Exception\InvalidPassword;
use App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


final class UserController extends Controller
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
        //private LogoutUseCase $logoutUseCase,
        private UserHttpMapper $mapper
    ) {}

    

    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Creating user with data: ', $request->all());
            
            // ✅ Validación mejorada con reglas únicas
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:8',
                'role' => 'sometimes|string|in:user,admin'
            ]);
            
            // Crear el comando manualmente
            $id = \Illuminate\Support\Str::uuid()->toString();
            $role = $validated['role'] ?? 'user';
            
            $command = new \App\Application\Users\Dto\Command\CreateUserCommand(
                name: $validated['name'],
                role: $role,
                email: $validated['email'],
                username: $validated['username'],
                password: $validated['password'],
                id: $id
            );
            
            // Ejecutar el servicio
            $userId = $this->createUser->execute($command);
            
            return response()->json(['id' => $userId], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Captura errores de validación de Laravel
            $errors = $e->errors();
            
            if (isset($errors['email'])) {
                return response()->json([
                    'error' => 'validation_error',
                    'message' => 'El email ya está en uso'
                ], 422);
            }
            
            if (isset($errors['username'])) {
                return response()->json([
                    'error' => 'validation_error', 
                    'message' => 'El nombre de usuario ya está en uso'
                ], 422);
            }
            
            return response()->json([
                'error' => 'validation_error',
                'message' => $errors
            ], 422);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // ✅ Captura errores de base de datos
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json([
                    'error' => 'duplicate_entry',
                    'message' => 'El usuario o email ya existe'
                ], 409); // 409 Conflict es más apropiado
            }
            
            Log::error('Database error creating user: ' . $e->getMessage());
            return response()->json([
                'error' => 'database_error',
                'message' => 'Error en la base de datos'
            ], 500);
            
        } catch (\Throwable $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return ApiExceptionHandler::handle($e);
        }
    }
        
    
    public function login(Request $request): JsonResponse
    {
        try {
            Log::info('Login attempt with data: ', $request->all());
            
            $validated = $request->validate([
                'username_or_email' => 'required|string',
                'password' => 'required|string',
            ]);

            $tokens = $this->loginUseCase->execute(
                $validated['username_or_email'], 
                $validated['password']
            );
            
            Log::info('Login successful for: ' . $validated['username_or_email']);
            return response()->json($tokens, 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'validation_error',
                'message' => $e->errors()
            ], 422);
        } catch (\RuntimeException $e) {
            // Captura específicamente errores de credenciales
            Log::warning('Login failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'authentication_error',
                'message' => 'Invalid credentials'
            ], 401);
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return ApiExceptionHandler::handle($e);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        try {
            Log::info('Refresh token attempt');
            
            $validated = $request->validate([
                'refresh_token' => 'required|string',
            ]);

            $tokens = $this->refreshTokenUseCase->execute($validated['refresh_token']);
            
            Log::info('Refresh token successful');
            return response()->json($tokens, 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'validation_error',
                'message' => $e->errors()
            ], 422);
        } catch (\RuntimeException $e) {
            // ✅ Captura específica para tokens inválidos - retorna 401
            Log::warning('Refresh token failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'invalid_token',
                'message' => $e->getMessage()
            ], 401);
        } catch (\Throwable $e) {
            Log::error('Refresh token error: ' . $e->getMessage());
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
            $query = new GetUserByIdQuery($id);
            $userResp = $this->getByIdUseCase->execute($query);
            if ($userResp === null) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $command = $this->mapper->toUpdateCommand($validated, $id);
            $this->updateUserUseCase->execute($command);
            return response()->json([], 204);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Captura errores de validación de Laravel
            $errors = $e->errors();
            
            if (isset($errors['email'])) {
                return response()->json([
                    'error' => 'validation_error',
                    'message' => 'El email ya está en uso'
                ], 422);
            }
            
            if (isset($errors['username'])) {
                return response()->json([
                    'error' => 'validation_error', 
                    'message' => 'El nombre de usuario ya está en uso'
                ], 422);
            }
            
            return response()->json([
                'error' => 'validation_error',
                'message' => $errors
            ], 422);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // ✅ Captura errores de base de datos
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json([
                    'error' => 'duplicate_entry',
                    'message' => 'El usuario o email ya existe'
                ], 409); // 409 Conflict es más apropiado
            }
            
            Log::error('Database error creating user: ' . $e->getMessage());
            return response()->json([
                'error' => 'database_error',
                'message' => 'Error en la base de datos'
            ], 500);
            
        } catch (\Throwable $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $query = new GetUserByIdQuery($id);
            $userResp = $this->getByIdUseCase->execute($query);
            if ($userResp === null) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $this->deleteUserUseCase->execute(new DeleteUserCommand($id));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function changePassword(ChangePasswordRequest $request, string $id): JsonResponse
    {
        try {
            $query = new GetUserByIdQuery($id);
            $userResp = $this->getByIdUseCase->execute($query);
            if ($userResp === null) {
                return response()->json(['error' => 'not_found', 'message' => 'User not found'], 404);
            }

            $payload = $request->validated();
            $command = new ChangePasswordCommand($id, $payload['currentPassword'], $payload['newPassword']);

            // Log seguro: nunca incluir contraseñas, solo presencia/flags
            Log::info('ChangePasswordCommand created', [
                'user_id' => $command->userId,
                'has_current_password' => !empty($payload['currentPassword']),
                'has_new_password' => !empty($payload['newPassword']),
            ]);

            $this->changePasswordUseCase->execute($command);

            // 204: No Content
            return response()->json(null, 204);
        } catch (InvalidPassword $e) {
            // Error de negocio -> 422 Unprocessable Entity
            return response()->json([
                'error' => 'invalid_current_password',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            // Delegate al handler centralizado (y evita exponer trace salvo en debug)
            return ApiExceptionHandler::handle($e);
        }
    }

    /*public function logout(string $id): JsonResponse
    {
        try {
            $this->logoutUseCase->execute($id);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }*/
}
