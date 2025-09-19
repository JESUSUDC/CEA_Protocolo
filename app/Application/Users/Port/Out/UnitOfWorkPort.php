<?php

namespace App\Application\Users\Port\Out;

interface UnitOfWorkPort
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
