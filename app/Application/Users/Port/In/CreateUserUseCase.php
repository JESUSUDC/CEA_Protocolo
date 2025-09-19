<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Command\CreateUserCommand;

interface CreateUserUseCase
{
    /**
     * Executes registration and returns created user id.
     *
     * @return string user id
     */
    public function execute(CreateUserCommand $command): string;
}
