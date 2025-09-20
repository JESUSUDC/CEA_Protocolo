<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Database\Eloquent\Repository;

use App\Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use App\Application\Users\Port\Out\UserRepositoryPort;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\Entity\User;
use App\Domain\Users\ValueObject\UserName;
use App\Domain\Users\ValueObject\Role;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\PasswordHash;

final class EloquentUserRepositoryAdapter implements UserRepositoryPort
{
    
    public function save(User $user): void
    {
        $m = UserModel::find($user->id()->toString()) ?? new UserModel();
        $m->id = $user->id()->toString();
        $m->name = $user->name()->toString();
        $m->role = $user->role()->toString();
        $m->email = $user->email()->toString();
        $m->username = $user->username()->toString();
        $m->password_hash = $user->passwordHash()->toString();  // ✅ Usa toString()
        $m->active = $user->isActive();
        $m->save();
    }

    private function toDomain(UserModel $m): User
    {
        return User::reconstitute(
            UserId::fromString($m->id),
            UserName::fromString($m->name),
            Role::fromString($m->role),
            Email::fromString($m->email),
            UserName::fromString($m->username),
            PasswordHash::fromHash($m->password_hash),  // ✅ Usa fromHash()
            (bool)$m->active
        );
    }

    public function findById(string $id): ?User
    {
        $m = UserModel::find($id);
        if (!$m) return null;
        return $this->toDomain($m);
    }

    public function findByUsername(string $username): ?User
    {
        $m = UserModel::where('username', strtolower($username))->first();
        if (!$m) return null;
        return $this->toDomain($m);
    }

    public function findByEmail(string $email): ?User
    {
        $m = UserModel::where('email', strtolower($email))->first();
        if (!$m) return null;
        return $this->toDomain($m);
    }

    public function delete(User $user): void
    {
        UserModel::destroy($user->id()->toString());
    }

    public function findAll(): array
    {
        $models = UserModel::orderBy('created_at', 'desc')->get();
        return $models->map(fn($m) => $this->toDomain($m))->all();
    }

}
