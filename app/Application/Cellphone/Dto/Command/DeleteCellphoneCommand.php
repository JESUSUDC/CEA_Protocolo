<?php
declare(strict_types=1);

namespace Application\Cellphone\Dto\Command;

final class DeleteCellphoneCommand
{
    public function __construct(public string $id) {}
}
