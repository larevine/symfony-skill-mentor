<?php

declare(strict_types=1);

namespace App\Domain\Event\Teacher;

use App\Domain\Event\DomainEventInterface;

class TeacherCreatedEvent implements DomainEventInterface
{
    public const NAME = 'teacher.created';

    public function __construct(
        private readonly int $teacher_id,
        private readonly array $teacher_info
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    public function getTeacherInfo(): array
    {
        return $this->teacher_info;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => $this->getEventName(),
            'payload' => [
                'teacher_id' => $this->teacher_id,
                'teacher_info' => $this->teacher_info,
            ],
        ];
    }
}
