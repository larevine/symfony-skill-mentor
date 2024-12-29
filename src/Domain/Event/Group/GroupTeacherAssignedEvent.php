<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupTeacherAssignedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $group_id,
        private readonly int $teacher_id
    ) {
    }

    public function getEventName(): string
    {
        return 'group.teacher_assigned';
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
                'teacher_id' => $this->teacher_id,
            ],
        ];
    }
}
