<?php
declare(strict_types=1);

namespace Domain\Users\Event;

use Domain\Users\ValueObject\UserId;

final class UserReactivated
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
