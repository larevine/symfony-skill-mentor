<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupSkillAddedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $group_id,
        private readonly int $skill_id,
        private readonly string $level
    ) {
    }

    public function getEventName(): string
    {
        return 'group.skill_added';
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getSkillId(): int
    {
        return $this->skill_id;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
                'skill_id' => $this->skill_id,
                'level' => $this->level,
            ],
        ];
    }
}
