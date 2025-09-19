<?php
declare(strict_types=1);

namespace Application\Port\Out;

interface UnitOfWorkPort
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
