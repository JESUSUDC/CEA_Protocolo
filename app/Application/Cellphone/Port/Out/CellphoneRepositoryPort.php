<?php
declare(strict_types=1);

namespace Application\Cellphone\Port\Out;

use Domain\Cellphone\Entity\Cellphone;

interface CellphoneRepositoryPort
{
    public function save(Cellphone $cellphone): void;

    public function update(Cellphone $cellphone): void;

    public function delete(string $id): void;

    public function findById(string $id): ?Cellphone;

    /**
     * @return Cellphone[]
     */
    public function listAll(int $limit = 50, int $offset = 0): array;
}
