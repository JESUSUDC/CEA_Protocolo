<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Dto\Command;

final class DeleteCellphoneCommand
{
    public function __construct(public string $id) {}
}
