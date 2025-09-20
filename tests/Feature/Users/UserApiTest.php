<?php
declare(strict_types=1);

namespace Tests\Feature\Users;

use Tests\TestCase;
use App\Application\Users\Port\In\CreateUserUseCase;
use App\Application\Users\Port\In\LoginUseCase;
use App\Application\Users\Port\In\RefreshTokenUseCase;
use App\Application\Users\Port\In\GetUserByIdUseCase;
use App\Application\Users\Port\In\ListUsersUseCase;
use App\Application\Users\Port\In\UpdateUserUseCase;
use App\Application\Users\Port\In\DeleteUserUseCase;
use App\Application\Users\Port\In\ChangePasswordUseCase;
use App\Domain\Users\Exception\UsernameAlreadyExists;
use App\Domain\Users\Exception\InvalidPassword;
use App\Infrastructure\Entrypoint\Rest\Middleware\JwtAuthMiddleware;
use App\Application\Users\Dto\Response\UserResponse;
use App\Application\Users\Dto\Response\UserListResponse;
use Symfony\Component\HttpFoundation\Response;

final class UserApiTest extends TestCase
{
    
    /*public function test_store_user_success(): void
    {
        $createMock = $this->createMock(CreateUserUseCase::class);
        $createMock->method('execute')->willReturn('generated-id-123');
        $this->app->instance(CreateUserUseCase::class, $createMock);

        $payload = [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'username' => 'juanp',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/v1/users', $payload);

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJson(['id' => 'generated-id-123']);
    }

    public function test_store_user_validation_error_returns_422(): void
    {
        $payload = [
            'name' => 'J', // Muy corto
            'email' => 'not-an-email', // Email inválido
            'username' => 'ab', // Muy corto
            'password' => '123', // Muy corto
        ];

        $response = $this->postJson('/api/v1/users', $payload);

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
    }*/

    public function test_login_returns_tokens(): void
    {
        $tokens = [
            'access_token' => 'access-abc',
            'refresh_token' => 'refresh-xyz',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $loginMock = $this->createMock(LoginUseCase::class);
        $loginMock->method('execute')->with('juanp', 'secret123')->willReturn($tokens);
        $this->app->instance(LoginUseCase::class, $loginMock);

        $response = $this->postJson('/api/v1/users/login', [
            'username_or_email' => 'juanp',
            'password' => 'secret123'
        ]);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in'])
                 ->assertJson(['access_token' => 'access-abc']);
    }

    public function test_refresh_token_success(): void
    {
        $tokens = [
            'access_token' => 'new-access',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $refreshMock = $this->createMock(RefreshTokenUseCase::class);
        $refreshMock->method('execute')->with('refresh-xyz')->willReturn($tokens);
        $this->app->instance(RefreshTokenUseCase::class, $refreshMock);

        $response = $this->postJson('/api/v1/users/refresh', ['refresh_token' => 'refresh-xyz']);
        $response->assertStatus(Response::HTTP_OK)->assertJson(['access_token' => 'new-access']);
    }

    public function test_show_user_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-1';
        $userDto = new UserResponse(
            id: $id,
            name: 'Juan',
            role: 'user',
            email: 'juan@example.com',
            username: 'juanp',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userDto);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("/api/v1/users/{$id}");
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure([
                     'id', 'name', 'role', 'email', 'username', 'active'
                 ])
                 ->assertJsonFragment([
                     'id' => $id,
                     'username' => 'juanp',
                     'email' => 'juan@example.com'
                 ]);
    }

    public function test_show_user_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'missing';
        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("/api/v1/users/{$id}");
        
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
                     'detail' => 'User not found'
                 ]);
    }

    public function test_index_returns_list(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $userA = new UserResponse(
            id: 'u1',
            name: 'User A',
            role: 'user',
            email: 'a@example.com',
            username: 'usera',
            active: true
        );

        $userB = new UserResponse(
            id: 'u2', 
            name: 'User B',
            role: 'admin',
            email: 'b@example.com',
            username: 'userb',
            active: true
        );

        $listResponse = new UserListResponse([$userA, $userB], 2);

        $listMock = $this->createMock(ListUsersUseCase::class);
        $listMock->method('execute')->willReturn($listResponse);
        $this->app->instance(ListUsersUseCase::class, $listMock);

        $response = $this->getJson('/api/v1/users');
        
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure([
                     'total',
                     'items' => [
                         '*' => [
                             'id', 'name', 'role', 'email', 'username', 'active'
                         ]
                     ]
                 ])
                 ->assertJsonFragment(['total' => 2])
                 ->assertJsonCount(2, 'items');
    }

    /*public function test_update_user_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-update';
        $userResp = new UserResponse(
            id: $id,
            name: 'Old Name',
            role: 'user',
            email: 'old@example.com',
            username: 'olduser',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $updateMock = $this->createMock(UpdateUserUseCase::class);
        $updateMock->expects($this->once())->method('execute');
        $this->app->instance(UpdateUserUseCase::class, $updateMock);

        $payload = [
            'username' => 'newname', 
            'email' => 'new@example.com',
            'name' => 'New Name'
        ];

        $response = $this->putJson("/api/v1/users/{$id}", $payload);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_update_user_conflict_username(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-update';
        $userResp = new UserResponse(
            id: $id,
            name: 'Test User',
            role: 'user',
            email: 'test@example.com',
            username: 'testuser',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $updateMock = $this->createMock(UpdateUserUseCase::class);
        $updateMock->method('execute')->willThrowException(new UsernameAlreadyExists('conflictname'));
        $this->app->instance(UpdateUserUseCase::class, $updateMock);

        $payload = ['username' => 'conflictname'];

        $response = $this->putJson("/api/v1/users/{$id}", $payload);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Business Rule Violation',
                     'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                 ]);
    }*/

    public function test_destroy_user_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'to-delete';
        $userResp = new UserResponse(
            id: $id,
            name: 'To Delete',
            role: 'user',
            email: 'delete@example.com',
            username: 'todelete',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $deleteMock = $this->createMock(DeleteUserUseCase::class);
        $deleteMock->expects($this->once())->method('execute');
        $this->app->instance(DeleteUserUseCase::class, $deleteMock);

        $response = $this->deleteJson("/api/v1/users/{$id}");
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_destroy_user_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'notfound';
        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->deleteJson("/api/v1/users/{$id}");
        
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
                     'detail' => 'User not found'
                 ]);
    }

    public function test_change_password_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-pass';
        $userResp = new UserResponse(
            id: $id,
            name: 'Password User',
            role: 'user',
            email: 'pass@example.com',
            username: 'passuser',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $changeMock = $this->createMock(ChangePasswordUseCase::class);
        $changeMock->expects($this->once())->method('execute');
        $this->app->instance(ChangePasswordUseCase::class, $changeMock);

        $payload = [
            'currentPassword' => 'old12345', 
            'newPassword' => 'new12345'
        ];
        
        $response = $this->postJson("/api/v1/users/{$id}/change-password", $payload);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_change_password_invalid_current(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-pass';
        $userResp = new UserResponse(
            id: $id,
            name: 'Password User',
            role: 'user',
            email: 'pass@example.com',
            username: 'passuser',
            active: true
        );

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $changeMock = $this->createMock(ChangePasswordUseCase::class);
        $changeMock->method('execute')->willThrowException(new InvalidPassword('Bad current password'));
        $this->app->instance(ChangePasswordUseCase::class, $changeMock);

        $payload = [
            'currentPassword' => 'wrong', 
            'newPassword' => 'new12345'
        ];
        
        $response = $this->postJson("/api/v1/users/{$id}/change-password", $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonStructure([
                     'type',
                     'title',
                     'status',
                     'detail',
                     'instance'
                 ])
                 ->assertJsonFragment([
                     'title' => 'Invalid Password',
                     'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                 ]);
    }

    /*public function test_protected_routes_require_authentication(): void
    {
        // No bypass middleware - debería fallar sin token
        $response = $this->getJson('/api/v1/users');
        
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