<?php
declare(strict_types=1);

namespace App\Domain\Users\Event;

use App\Domain\Users\ValueObject\UserId;

final class UserRegistered
{
    private UserId $userId;
    private string $email;
    private string $username;

    public function __construct(UserId $userId, string $email, string $username)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->username = $username;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function username(): string
    {
        return $this->username;
    }
}
