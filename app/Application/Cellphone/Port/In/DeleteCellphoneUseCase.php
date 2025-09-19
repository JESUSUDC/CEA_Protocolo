<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Port\In;

use App\Application\Cellphone\Dto\Command\DeleteCellphoneCommand;

interface DeleteCellphoneUseCase
{
    public function execute(DeleteCellphoneCommand $command): void;
}
