<?php

declare(strict_types=1);

namespace App\Domain\Event\Group;

use App\Domain\Event\DomainEventInterface;

class GroupStudentAddedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $group_id,
        private readonly int $student_id
    ) {
    }

    public function getEventName(): string
    {
        return 'group.student_added';
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getStudentId(): int
    {
        return $this->student_id;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'group_id' => $this->group_id,
                'student_id' => $this->student_id,
            ],
        ];
    }
}
