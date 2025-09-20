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
use ReflectionClass;

final class UserApiTest extends TestCase
{
    private function makeUserResponse(array $data)
    {
        // Creamos una instancia del DTO UserResponse sin llamar al constructor
        $fqcn = \App\Application\Users\Dto\Response\UserResponse::class;
        $ref = new ReflectionClass($fqcn);
        $instance = $ref->newInstanceWithoutConstructor();

        // Seteamos propiedades públicas/privadas por reflexión si existen
        foreach ($data as $k => $v) {
            if ($ref->hasProperty($k)) {
                $prop = $ref->getProperty($k);
                $prop->setAccessible(true);
                $prop->setValue($instance, $v);
            } else {
                // Si la clase permite propiedades dinámicas (PHP < 8.2) esto funcionará,
                // en caso contrario lo ignoramos (pero en la mayoría de DTOs las props existen).
                $instance->{$k} = $v;
            }
        }

        return $instance;
    }

    public function test_store_user_success(): void
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

        // Nota: routes/api.php por defecto usa prefijo "api", por eso usamos /api/v1
        $response = $this->postJson('/api/v1/users', $payload);

        $response->assertStatus(201)
                 ->assertJson(['id' => 'generated-id-123']);
    }

    public function test_store_user_validation_error_returns_422(): void
    {
        $payload = [
            'name' => 'J',
            'email' => 'not-an-email',
            'username' => 'ab',
            'password' => '123',
        ];

        $response = $this->postJson('/api/v1/users', $payload);

        $response->assertStatus(422);
        $this->assertArrayHasKey('error', $response->json());
    }

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

        $response->assertStatus(200)
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
        $response->assertStatus(200)->assertJson(['access_token' => 'new-access']);
    }

    public function test_show_user_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-1';
        $userDto = $this->makeUserResponse([
            'id' => $id,
            'name' => 'Juan',
            'role' => 'user',
            'email' => 'juan@example.com',
            'username' => 'juanp',
            'active' => true
        ]);

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userDto);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("/api/v1/users/{$id}");
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $id, 'username' => 'juanp']);
    }

    public function test_show_user_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'missing';
        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->getJson("/api/v1/users/{$id}");
        $response->assertStatus(404)
                 ->assertJson(['message' => 'User not found']);
    }

    public function test_index_returns_list(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $userA = $this->makeUserResponse(['id' => 'u1', 'name' => 'A', 'role' => 'user', 'email' => 'a@x.com', 'username' => 'a', 'active' => true]);
        $userB = $this->makeUserResponse(['id' => 'u2', 'name' => 'B', 'role' => 'user', 'email' => 'b@x.com', 'username' => 'b', 'active' => true]);

        $listMock = $this->createMock(ListUsersUseCase::class);
        $listMock->method('execute')->willReturn((object)[
            'items' => [$userA, $userB],
            'total' => 2
        ]);
        $this->app->instance(ListUsersUseCase::class, $listMock);

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200)
                 ->assertJsonStructure(['total', 'items'])
                 ->assertJsonFragment(['total' => 2]);
    }

    public function test_update_user_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-update';
        $userResp = $this->makeUserResponse(['id' => $id]);

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $updateMock = $this->createMock(UpdateUserUseCase::class);
        $updateMock->expects($this->once())->method('execute');
        $this->app->instance(UpdateUserUseCase::class, $updateMock);

        $payload = ['username' => 'newname', 'email' => 'new@example.com'];

        $response = $this->putJson("/api/v1/users/{$id}", $payload);
        $response->assertStatus(204)->assertNoContent();
    }

    public function test_update_user_conflict_username(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-update';
        $userResp = $this->makeUserResponse(['id' => $id]);

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($userResp);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $updateMock = $this->createMock(UpdateUserUseCase::class);
        $updateMock->method('execute')->will($this->throwException(new UsernameAlreadyExists('conflictname')));
        $this->app->instance(UpdateUserUseCase::class, $updateMock);

        $response = $this->putJson("/api/v1/users/{$id}", ['username' => 'conflictname']);

        $response->assertStatus(409);
        $this->assertArrayHasKey('error', $response->json());
    }

    public function test_destroy_user_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'to-delete';

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($this->makeUserResponse(['id' => $id]));
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $deleteMock = $this->createMock(DeleteUserUseCase::class);
        $deleteMock->expects($this->once())->method('execute');
        $this->app->instance(DeleteUserUseCase::class, $deleteMock);

        $response = $this->deleteJson("/api/v1/users/{$id}");
        $response->assertStatus(204)->assertNoContent();
    }

    public function test_destroy_user_not_found(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'notfound';
        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn(null);
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $response = $this->deleteJson("/api/v1/users/{$id}");
        $response->assertStatus(404)->assertJson(['message' => 'User not found']);
    }

    public function test_change_password_success(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-pass';

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($this->makeUserResponse(['id' => $id]));
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $changeMock = $this->createMock(ChangePasswordUseCase::class);
        $changeMock->expects($this->once())->method('execute');
        $this->app->instance(ChangePasswordUseCase::class, $changeMock);

        $payload = ['currentPassword' => 'old12345', 'newPassword' => 'new12345'];
        $response = $this->postJson("/api/v1/users/{$id}/change-password", $payload);
        $response->assertStatus(204)->assertNoContent();
    }

    public function test_change_password_invalid_current(): void
    {
        $this->withoutMiddleware(JwtAuthMiddleware::class);

        $id = 'user-pass';

        $getByIdMock = $this->createMock(GetUserByIdUseCase::class);
        $getByIdMock->method('execute')->willReturn($this->makeUserResponse(['id' => $id]));
        $this->app->instance(GetUserByIdUseCase::class, $getByIdMock);

        $changeMock = $this->createMock(ChangePasswordUseCase::class);
        $changeMock->method('execute')->will($this->throwException(new InvalidPassword('Bad current password')));
        $this->app->instance(ChangePasswordUseCase::class, $changeMock);

        $payload = ['currentPassword' => 'wrong', 'newPassword' => 'new12345'];
        $response = $this->postJson("/api/v1/users/{$id}/change-password", $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'invalid_current_password']);
    }
}
