<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Command\UpdateUserCommand;

interface UpdateUserUseCase
{
    public function execute(UpdateUserCommand $command): void;
}
