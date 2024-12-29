<?php

declare(strict_types=1);

namespace App\Domain\Event\Student;

use App\Domain\Event\DomainEventInterface;

class StudentJoinedGroupEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $student_id,
        private readonly int $group_id
    ) {
    }

    public function getEventName(): string
    {
        return 'student.joined_group';
    }

    public function getStudentId(): int
    {
        return $this->student_id;
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
                'student_id' => $this->student_id,
                'group_id' => $this->group_id,
            ],
        ];
    }
}
