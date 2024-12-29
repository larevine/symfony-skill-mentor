<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupUpdatedEvent implements DomainEventInterface
{
    public const NAME = 'group.updated';

    public function __construct(
        private readonly int $group_id,
        private readonly array $group_info
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getGroupInfo(): array
    {
        return $this->group_info;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
                'group_info' => $this->group_info,
            ],
        ];
    }
}
