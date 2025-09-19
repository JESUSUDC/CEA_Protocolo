<?php

namespace Application\Users\Port\Out;

interface PasswordHasherPort
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $hashedPassword): bool;
}
