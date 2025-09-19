<?php
declare(strict_types=1);

namespace Application\Users\Service;

use Application\Users\Port\In\RequestPasswordUseCase;
use Application\Port\Out\UserRepositoryPort as OutUserRepository;

final class RequestPasswordService implements RequestPasswordUseCase
{
    public function __construct(private OutUserRepository $userRepository, /* plus mailer, token repo */) {}

    public function execute(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            // don't reveal user existence; either silent or send generic email.
            return;
        }

        // Generate a password reset token and persist it (not implemented here).
        // Send email with reset link via an Outbound Mailer port.
    }
}
