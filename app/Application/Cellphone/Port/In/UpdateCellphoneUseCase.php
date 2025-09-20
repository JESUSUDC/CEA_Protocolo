<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Port\In;

use App\Application\Cellphone\Dto\Command\UpdateCellphoneCommand;

interface UpdateCellphoneUseCase
{
    public function execute(UpdateCellphoneCommand $command): void; // ✅ Solo el comando
}