<?php
declare(strict_types=1);

namespace Application\Cellphone\Dto\Query;

final class GetCellphoneByIdQuery
{
    public function __construct(public string $id) {}
}
