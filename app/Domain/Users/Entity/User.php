<?php
declare(strict_types=1);

namespace App\Domain\Users\Entity;

use App\Domain\Shared\AggregateRoot;
use App\Domain\Users\ValueObject\UserId;
use App\Domain\Users\ValueObject\UserName;
use App\Domain\Users\ValueObject\Role;
use App\Domain\Users\ValueObject\PasswordHash;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\Event\UserRegistered;
use App\Domain\Users\Event\UserPasswordChanged;
use App\Domain\Users\Event\UserDeactivated;
use App\Domain\Users\Event\UserReactivated;
use App\Domain\Users\Event\UserRenamed;
use App\Domain\Users\Event\UserRoleAssigned;
use App\Domain\Users\Exception\UserAlreadyActive;
use App\Domain\Users\Exception\UserAlreadyInactive;
use App\Domain\Users\Exception\InvalidPassword;
use App\Domain\Users\Service\Contracts\PasswordHasher;

final class User extends AggregateRoot
{
    private UserId $id;
    private UserName $name;
    private Role $role;
    private Email $email;
    private UserName $username;
    private PasswordHash $password;
    private bool $active;

    private function __construct(
        UserId $id,
        UserName $name,
        Role $role,
        Email $email,
        UserName $username,
        PasswordHash $password,
        bool $active
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->role = $role;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
        $this->active = $active;
    }

    /**
     * Factory for registering a new user.
     * Application layer must validate password strength and produce PasswordHash via PasswordHasher.
     */
    public static function register(
        UserId $id,
        UserName $name,
        Role $role,
        Email $email,
        UserName $username,
        PasswordHash $passwordHash
    ): self {
        $user = new self($id, $name, $role, $email, $username, $passwordHash, true);
        $user->recordEvent(new UserRegistered($id, $email->toString(), $username->toString()));
        return $user;
    }

    public function id(): UserId { return $this->id; }
    public function name(): UserName { return $this->name; }
    public function role(): Role { return $this->role; }
    public function email(): Email { return $this->email; }
    public function username(): UserName { return $this->username; }
    public function passwordHash(): PasswordHash { return $this->password; }
    public function isActive(): bool { return $this->active; }

    /**
     * Change password: domain ensures new hash differs from old (semantic rule).
     */
    public function changePassword(PasswordHash $newHash): void
    {
        if ($this->password->equals($newHash)) {
            throw new InvalidPassword('New password must be different from current.');
        }

        $this->password = $newHash;
        $this->recordEvent(new UserPasswordChanged($this->id));
    }

    public function deactivate(): void
    {
        if (!$this->active) {
            throw new UserAlreadyInactive('User is already inactive.');
        }
        $this->active = false;
        $this->recordEvent(new UserDeactivated($this->id));
    }

    public function reactivate(): void
    {
        if ($this->active) {
            throw new UserAlreadyActive('User is already active.');
        }
        $this->active = true;
        $this->recordEvent(new UserReactivated($this->id));
    }

    public function rename(UserName $newName): void
    {
        if ($this->name->equals($newName)) {
            return; // idempotent
        }
        $this->name = $newName;
        $this->recordEvent(new UserRenamed($this->id, $newName->toString()));
    }

    public function assignRole(Role $role): void
    {
        if ($this->role->equals($role)) {
            return;
        }
        $this->role = $role;
        $this->recordEvent(new UserRoleAssigned($this->id, $role));
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }
        $this->email = $newEmail;
        // we could emit an EmailChanged event — omitted for brevity
    }

    /**
     * Domain-level password verification (delegates to a PasswordHasher).
     * We don't implement hashing here; application injects a hasher.
     */
    public function verifyPassword(string $plain, PasswordHasher $hasher): bool
    {
        return $hasher->verify($plain, $this->password);
    }
}
