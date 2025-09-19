<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

use App\Application\Users\Dto\Command\DeleteUserCommand;

interface DeleteUserUseCase
{
    public function execute(DeleteUserCommand $command): void;
}
