<?php
declare(strict_types=1);

namespace App\Application\Users\Port\In;

interface RequestPasswordUseCase
{
    public function execute(string $email): void;
}
