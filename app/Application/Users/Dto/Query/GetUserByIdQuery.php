<?php
declare(strict_types=1);

namespace App\Application\Users\Dto\Query;

final class GetUserByIdQuery
{
    public function __construct(public string $userId) {}
}
