<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Database\Eloquent\UnitOfWork;

use Illuminate\Support\Facades\DB;
use Application\Users\Port\Out\UnitOfWorkPort;

final class LaravelUnitOfWorkAdapter implements UnitOfWork
{
    public function transactional(callable $work)
    {
        return DB::transaction($work);
    }
}
