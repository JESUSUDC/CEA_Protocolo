<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Users\Mapper;

use App\Application\Users\Dto\Command\CreateUserCommand as CommandCreateUserCommand;
use Illuminate\Support\Str;
use App\Application\Users\Dto\Command\CreateUserCommand;
use App\Application\Users\Dto\Command\UpdateUserCommand;
use App\Application\Users\Response\UserResponse;
use App\Domain\Users\ValueObject\Role;

final class UserHttpMapper
{
    /**
     * Map validated HTTP payload to Application CreateUserCommand
     *
     * @param array $dto validated request data
     * @return CreateUserCommand
     */
    public function toCreateCommand(array $dto): CommandCreateUserCommand
    {
        $id = $dto['id'] ?? Str::uuid()->toString();
        $role = $dto['role'] ?? 'user';

        return new CreateUserCommand(
            name: $dto['name'],
            role: $role,
            email: $dto['email'],
            username: $dto['username'],
            password: $dto['password'],
            id: $id
        );
    }

    /**
     * Map validated HTTP payload to UpdateUserCommand
     *
     * @param array $dto
     * @param string $userId
     * @return UpdateUserCommand
     */
    public function toUpdateCommand(array $dto, string $userId): UpdateUserCommand
    {
        return new UpdateUserCommand(
            userId: $userId,
            name: $dto['name'] ?? null,
            role: $dto['role'] ?? null,
            email: $dto['email'] ?? null,
            username: $dto['username'] ?? null
        );
    }

    /**
     * Map Application UserResponse to HTTP array
     */
    public function toHttp(UserResponse $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'role' => $r->role,
            'email' => $r->email,
            'username' => $r->username,
            'active' => $r->active
        ];
    }

    /**
     * Map list of UserResponse to HTTP payload
     *
     * @param UserResponse[] $items
     * @return array
     */
    public function toHttpList(array $items, int $total = 0): array
    {
        return [
            'total' => $total,
            'items' => array_map(fn(UserResponse $u) => $this->toHttp($u), $items)
        ];
    }
}
