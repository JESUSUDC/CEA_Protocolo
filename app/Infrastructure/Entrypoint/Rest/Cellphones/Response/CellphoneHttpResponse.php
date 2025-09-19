<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Response;

final class CellphoneHttpResponse
{
    public function __construct(private array $payload)
    {
    }

    public function toArray(): array
    {
        return $this->payload;
    }
}
