<?php

declare(strict_types=1);

namespace App\Domain\Dto\Message;

class TeacherSkillsMessage
{
    /** @var array<array{skill_id: int, level: string}> */
    private array $skills;
    private int $teacher_id;

    /**
     * @param array<array{skill_id: int, level: string}> $skills
     */
    public function __construct(int $teacher_id, array $skills)
    {
        $this->teacher_id = $teacher_id;
        $this->skills = $skills;
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    /**
     * @return array<array{skill_id: int, level: string}>
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param array{teacher_id: int, skills: array<array{skill_id: int, level: string}>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            teacher_id: $data['teacher_id'],
            skills: $data['skills']
        );
    }
}
