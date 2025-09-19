<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Port\In;

use App\Application\Cellphone\Dto\Command\CreateCellphoneCommand;

interface RegisterCellphoneUseCase
{
    /**
     * Registers a cellphone and returns its id.
     */
    public function execute(CreateCellphoneCommand $command): string;
}
