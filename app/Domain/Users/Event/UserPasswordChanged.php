<?php
declare(strict_types=1);

namespace App\Domain\Users\Event;

use App\Domain\Users\ValueObject\UserId;

final class UserPasswordChanged
{
    private UserId $userId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
