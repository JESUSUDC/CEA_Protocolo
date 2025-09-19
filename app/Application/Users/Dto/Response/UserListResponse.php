<?php
declare(strict_types=1);

namespace App\Application\Users\Response;

final class UserListResponse
{
    /**
     * @param UserResponse[] $items
     */
    public function __construct(public array $items, public int $total = 0) {}
}
