<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\In;

use Application\Cellphone\Dto\Command\UpdateCellphoneCommand;

interface UpdateCellphoneUseCase
{
    public function execute(UpdateCellphoneCommand $command): void;
}
