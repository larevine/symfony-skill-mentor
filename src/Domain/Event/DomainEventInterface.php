<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface DomainEventInterface
{
    public function getEventName(): string;

    public function toArray(): array;
}
