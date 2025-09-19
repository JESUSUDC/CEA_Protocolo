<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\ResetPasswordUseCase;

final class ResetPasswordService implements ResetPasswordUseCase
{
    public function __construct(/* token repo, user repo, hasher, uow */) {}

    public function execute(string $token, string $newPassword): void
    {
        // Validate token, find user, check policy, hash new password and save.
    }
}
