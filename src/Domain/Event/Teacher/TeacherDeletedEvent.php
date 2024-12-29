<?php

declare(strict_types=1);

namespace App\Domain\Event\Teacher;

use App\Domain\Event\DomainEventInterface;

class TeacherDeletedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $teacher_id
    ) {
    }

    public function getEventName(): string
    {
        return 'teacher.deleted';
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'teacher_id' => $this->teacher_id,
            ],
        ];
    }
}
