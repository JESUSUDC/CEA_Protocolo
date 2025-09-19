<?php
declare(strict_types=1);

namespace Application\Users\Port\In;

use Application\Users\Dto\Command\ChangePasswordCommand;

interface ChangePasswordUseCase
{
    public function execute(ChangePasswordCommand $command): void;
}
