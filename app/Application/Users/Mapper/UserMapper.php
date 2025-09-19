<?php
declare(strict_types=1);

namespace Application\Users\Mapper;

use Domain\Users\Entity\User;
use Application\Users\Response\UserResponse;

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
