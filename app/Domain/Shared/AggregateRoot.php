<?php
declare(strict_types=1);

namespace App\Domain\Shared;

abstract class AggregateRoot
{
    /**
     * @var array<object> Domain events recorded by the aggregate
     */
    private array $domainEvents = [];

    protected function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Return recorded events and clear the list (caller decides delivery).
     * Application layer will read and dispatch them.
     *
     * @return array<object>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
