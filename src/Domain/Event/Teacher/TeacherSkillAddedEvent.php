<?php

declare(strict_types=1);

namespace App\Domain\Event\Teacher;

use App\Domain\Event\DomainEventInterface;

class TeacherSkillAddedEvent implements DomainEventInterface
{
    public function __construct(
        private readonly int $teacher_id,
        private readonly int $skill_id,
        private readonly string $level
    ) {
    }

    public function getEventName(): string
    {
        return 'teacher.skill_added';
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    public function getSkillId(): int
    {
        return $this->skill_id;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'teacher_id' => $this->teacher_id,
                'skill_id' => $this->skill_id,
                'level' => $this->level,
            ],
        ];
    }
}
