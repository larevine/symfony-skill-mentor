<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupDeletedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $group_id
    ) {
    }

    public function getEventName(): string
    {
        return 'group.deleted';
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
            ],
        ];
    }
}
