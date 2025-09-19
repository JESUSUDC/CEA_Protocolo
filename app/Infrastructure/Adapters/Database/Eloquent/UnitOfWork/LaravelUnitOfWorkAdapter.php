<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Database\Eloquent\UnitOfWork;

use Illuminate\Support\Facades\DB;
use App\Application\Users\Port\Out\UnitOfWorkPort;

final class LaravelUnitOfWorkAdapter implements UnitOfWorkPort
{
    private bool $transactionStarted = false;

    public function begin(): void
    {
        if (!$this->transactionStarted) {
            DB::beginTransaction();
            $this->transactionStarted = true;
        }
    }

    public function commit(): void
    {
        if ($this->transactionStarted) {
            DB::commit();
            $this->transactionStarted = false;
        }
    }

    public function rollback(): void
    {
        if ($this->transactionStarted) {
            DB::rollBack();
            $this->transactionStarted = false;
        }
    }
}
