<?php

declare(strict_types=1);

namespace App\Domain\Event\Student;

use App\Domain\Event\DomainEventInterface;

class StudentSkillAddedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $student_id,
        private readonly int $skill_id,
        private readonly string $level
    ) {
    }

    public function getEventName(): string
    {
        return 'student.skill_added';
    }

    public function getStudentId(): int
    {
        return $this->student_id;
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
                'student_id' => $this->student_id,
                'skill_id' => $this->skill_id,
                'level' => $this->level,
            ],
        ];
    }
}
