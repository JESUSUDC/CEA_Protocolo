<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\In;

use Application\Cellphone\Dto\Command\DeleteCellphoneCommand;

interface DeleteCellphoneUseCase
{
    public function execute(DeleteCellphoneCommand $command): void;
}
