<?php

declare(strict_types=1);

namespace App\Domain\Event\Student;

use App\Domain\Event\DomainEventInterface;

class StudentDeletedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $student_id
    ) {
    }

    public function getEventName(): string
    {
        return 'student.deleted';
    }

    public function getStudentId(): int
    {
        return $this->student_id;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'student_id' => $this->student_id,
            ],
        ];
    }
}
