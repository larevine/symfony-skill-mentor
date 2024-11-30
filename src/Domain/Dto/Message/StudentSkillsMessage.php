<?php

declare(strict_types=1);

namespace App\Domain\Dto\Message;

readonly class StudentSkillsMessage
{
    /**
     * @param array<array{id: int, level: int}> $skills
     */
    public function __construct(
        private int $student_id,
        private array $skills,
    ) {
    }

    public function getStudentId(): int
    {
        return $this->student_id;
    }

    /**
     * @return array<array{id: int, level: int}>
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param array{student_id: int, skills: array<array{id: int, level: int}>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['student_id'],
            $data['skills'],
        );
    }
}
