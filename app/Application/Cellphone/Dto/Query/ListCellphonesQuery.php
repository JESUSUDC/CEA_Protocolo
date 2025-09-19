<?php
declare(strict_types=1);

namespace Application\Cellphone\Dto\Query;

final class ListCellphonesQuery
{
    public function __construct(public int $limit = 50, public int $offset = 0) {}
}
