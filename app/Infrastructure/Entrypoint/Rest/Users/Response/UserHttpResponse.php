<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Users\Response;

final class UserHttpResponse
{
    public function __construct(private array $payload) {}

    public function toArray(): array { return $this->payload; }
}
