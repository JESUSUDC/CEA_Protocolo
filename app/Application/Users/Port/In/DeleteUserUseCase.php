<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Command\DeleteUserCommand;

interface DeleteUserUseCase
{
    public function execute(DeleteUserCommand $command): void;
}
