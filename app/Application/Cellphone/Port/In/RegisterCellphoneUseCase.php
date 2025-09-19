<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\In;

use Application\Cellphone\Dto\Command\CreateCellphoneCommand;

interface RegisterCellphoneUseCase
{
    /**
     * Registers a cellphone and returns its id.
     */
    public function execute(CreateCellphoneCommand $command): string;
}
