<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Database\Eloquent\Repository;

use Infrastructure\Adapters\Database\Eloquent\Model\UserModel;
use Domain\Users\ValueObject\UserId;
use Domain\Users\ValueObject\UserName;
use Domain\Users\ValueObject\Email;
use Domain\Users\ValueObject\Role;
use Domain\Users\ValueObject\PasswordHash;
use Domain\Users\Entity\User as UserEntity;
use Carbon\Carbon;

// NOTE: adapt interface name
use Application\Users\Port\Out\UserRepositoryPort;

final class EloquentUserRepositoryAdapter implements UserRepository
{
    public function __construct(private UserModel $model)
    {
    }

    public function save(UserEntity $user): void
    {
        $m = UserModel::find($user->id()->toString()) ?? new UserModel();
        $m->id = $user->id()->toString();
        $m->name = $user->name()->toString();
        $m->role = $user->role()->toString();
        $m->email = $user->email()->toString();
        $m->username = $user->username()->toString();
        $m->password_hash = $user->passwordHash()->toString();
        $m->active = $user->isActive();
        $m->updated_at = Carbon::now();
        $m->save();
    }

    public function findById(UserId $id): ?UserEntity
    {
        $m = UserModel::find($id->toString());
        if (!$m) {
            return null;
        }
        return $this->toDomain($m);
    }

    public function findByUsername(string $username): ?UserEntity
    {
        $m = UserModel::where('username', $username)->first();
        if (!$m) {
            return null;
        }
        return $this->toDomain($m);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $m = UserModel::where('email', strtolower($email))->first();
        if (!$m) {
            return null;
        }
        return $this->toDomain($m);
    }

    public function remove(UserEntity $user): void
    {
        UserModel::destroy($user->id()->toString());
    }

    private function toDomain(UserModel $m): UserEntity
    {
        return UserEntity::register(
            UserId::fromString($m->id),
            UserName::fromString($m->name),
            Role::fromString($m->role),
            Email::fromString($m->email),
            UserName::fromString($m->username),
            PasswordHash::fromHash($m->password_hash)
        );
        // Note: register() sets active = true by factory; if DB has active=false then
        // you could provide a reconstitution method in domain (recommended).
    }
}
