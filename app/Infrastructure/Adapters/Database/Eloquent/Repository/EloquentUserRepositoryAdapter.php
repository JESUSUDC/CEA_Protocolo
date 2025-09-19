<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Database\Eloquent\Repository;

use Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use Application\Users\Port\Out\UserRepositoryPort;
use Domain\Users\ValueObject\UserId;
use Domain\Users\Entity\User;
use Domain\Users\ValueObject\UserName;
use Domain\Users\ValueObject\Role;
use Domain\Users\ValueObject\Email;
use Domain\Users\ValueObject\PasswordHash;

final class EloquentUserRepositoryAdapter implements UserRepositoryPort
{
    public function __construct(private UserModel $model)
    {
    }

    public function save(User $user): void
    {
        $m = UserModel::find($user->id()->toString()) ?? new UserModel();
        $m->id = $user->id()->toString();
        $m->name = $user->name()->toString();
        $m->role = $user->role()->toString();
        $m->email = $user->email()->toString();
        $m->username = $user->username()->toString();
        $m->password_hash = $user->passwordHash()->toString();
        $m->active = $user->isActive();
        $m->save();
    }

    public function findById(string $id): ?User
    {
        $m = UserModel::find($id);
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

    private function toDomain(UserModel $m): User
    {
        // Preferimos reconstitute (no usar register) para preservar 'active' y hash reales.
        return User::reconstitute(
            UserId::fromString($m->id),
            UserName::fromString($m->name),
            Role::fromString($m->role),
            Email::fromString($m->email),
            UserName::fromString($m->username),
            PasswordHash::fromHash($m->password_hash),
            (bool)$m->active
        );
    }
}
