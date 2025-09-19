<?php

namespace Application\Users\Port\Out;

use Domain\Users\Entity\User;

interface UserRepositoryPort
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function delete(User $user): void;

    /**
     * @return User[]
     */
    public function findAll(): array;
}
