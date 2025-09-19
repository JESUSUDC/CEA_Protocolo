<?php
declare(strict_types=1);

namespace Domain\Users\Event;

use Domain\Users\ValueObject\UserId;

final class UserRenamed
{
    private UserId $userId;
    private string $newName;

    public function __construct(UserId $userId, string $newName)
    {
        $this->userId = $userId;
        $this->newName = $newName;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function newName(): string
    {
        return $this->newName;
    }
}
