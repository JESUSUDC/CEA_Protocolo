<?php
declare(strict_types=1);

namespace Domain\Cellphone\Event;

use Domain\Cellphone\ValueObject\CellphoneId;

final class CellphoneSpecificationsUpdated
{
    private CellphoneId $id;
    private \DateTimeImmutable $when;

    public function __construct(CellphoneId $id)
    {
        $this->id = $id;
        $this->when = new \DateTimeImmutable();
    }

    public function id(): CellphoneId { return $this->id; }
    public function when(): \DateTimeImmutable { return $this->when; }
}
