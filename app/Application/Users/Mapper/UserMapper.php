<?php
declare(strict_types=1);

namespace App\Application\Users\Mapper;

use App\Domain\Users\Entity\User;
use App\Application\Users\Dto\Response\UserResponse;

final class UserMapper
{
    public function toResponse(User $user): UserResponse
    {
        return new UserResponse(
            $user->id()->toString(),
            $user->name()->toString(),
            $user->role()->toString(),
            $user->email()->toString(),
            $user->username()->toString(),
            $user->isActive()
        );
    }

    /**
     * @param User[] $users
     * @return UserResponse[]
     */
    public function toResponses(array $users): array
    {
        return array_map(fn(User $u) => $this->toResponse($u), $users);
    }
}
