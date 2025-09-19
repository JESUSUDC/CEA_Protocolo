<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

use App\Application\Users\Dto\Command\UpdateUserCommand;

interface UpdateUserUseCase
{
    public function execute(UpdateUserCommand $command): void;
}
