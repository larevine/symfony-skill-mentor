<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Student;
use App\Domain\Entity\SkillProficiency;

readonly class StudentResponse
{
    /**
     * @param array<SkillResponse> $skills
     */
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public array $skills = [],
    ) {
    }

    public static function fromEntity(Student $student): self
    {
        return new self(
            id: $student->getId() ?? 0,
            first_name: $student->getFirstName(),
            last_name: $student->getLastName(),
            email: $student->getEmail(),
            skills: array_map(
                static fn (SkillProficiency $skill) => SkillResponse::fromSkillProficiency($skill),
                $student->getSkills()->toArray()
            ),
        );
    }
}
