<?php
declare(strict_types=1);

namespace Application\Users\Dto\Query;

final class ListUserQuery
{
    public function __construct(public int $limit = 50, public int $offset = 0) {}
}
