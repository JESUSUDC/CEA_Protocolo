<?php

namespace App\Application\Users\Port\Out;

use App\Domain\Users\Entity\User;

interface UserRepositoryPort
{
    public function save(User $user): void;

    public function update(User $user): void;

    public function findById(string $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function delete(User $user): void;

    /**
     * @return User[]
     */
    public function findAll(): array;
}
