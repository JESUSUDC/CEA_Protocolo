<?php
declare(strict_types=1);

namespace App\Domain\Users\Event;

use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\ValueObject\Role;

final class UserRoleAssigned
{
    private UserId $userId;
    private Role $role;

    public function __construct(UserId $userId, Role $role)
    {
        $this->userId = $userId;
        $this->role = $role;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function role(): Role
    {
        return $this->role;
    }
}
