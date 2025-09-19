<?php
declare(strict_types=1);

namespace Application\Cellphone\Response;

final class CellphoneListResponse
{
    /**
     * @param CellphoneResponse[] $items
     */
    public function __construct(public array $items, public int $total = 0) {}
}
