<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Mapper;

use Illuminate\Support\Str;
use Domain\Users\ValueObject\Role;

final class UserHttpMapper
{
    public function toRegisterCommand(array $dto): array
    {
        return [
            'id' => $dto['id'] ?? Str::uuid()->toString(),
            'name' => $dto['name'],
            'role' => $dto['role'] ?? Role::fromString('user')->toString(),
            'email' => $dto['email'],
            'username' => $dto['username'],
            'password' => $dto['password'],
        ];
    }

    public function toHttp($userEntity): array
    {
        // expects domain entity User
        return [
            'id' => $userEntity->id()->toString(),
            'name' => $userEntity->name()->toString(),
            'role' => $userEntity->role()->toString(),
            'email' => $userEntity->email()->toString(),
            'username' => $userEntity->username()->toString(),
            'active' => $userEntity->isActive()
        ];
    }
}
