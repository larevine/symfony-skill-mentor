<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupSkillRemovedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $group_id,
        private readonly int $skill_id
    ) {
    }

    public function getEventName(): string
    {
        return 'group.skill_removed';
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getSkillId(): int
    {
        return $this->skill_id;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
                'skill_id' => $this->skill_id,
            ],
        ];
    }
}
