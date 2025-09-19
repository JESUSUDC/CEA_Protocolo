<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

use App\Application\Users\Dto\Command\ChangePasswordCommand;

interface ChangePasswordUseCase
{
    public function execute(ChangePasswordCommand $command): void;
}
