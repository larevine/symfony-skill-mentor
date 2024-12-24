<?php

declare(strict_types=1);

namespace App\Domain\Event\Student;

use App\Domain\Event\DomainEventInterface;

class StudentUpdatedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $student_id,
        private readonly array $student_info
    ) {
    }

    public function getEventName(): string
    {
        return 'student.updated';
    }

    public function getStudentId(): int
    {
        return $this->student_id;
    }

    public function getStudentInfo(): array
    {
        return $this->student_info;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'student_id' => $this->student_id,
                'student_info' => $this->student_info,
            ],
        ];
    }
}
