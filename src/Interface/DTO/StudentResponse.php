<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Student;
use App\Domain\Entity\SkillProficiency;
use JsonSerializable;

readonly class StudentResponse implements JsonSerializable
{
    /**
     * @param array<SkillResponse> $skills
     */
    public function __construct(
        public ?int $id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public array $skills = [],
    ) {
    }

    public static function fromEntity(Student $student): self
    {
        return new self(
            id: $student->getId(),
            first_name: $student->getFirstName(),
            last_name: $student->getLastName(),
            email: $student->getEmail(),
            skills: array_map(
                static fn (SkillProficiency $skill) => SkillResponse::fromSkillProficiency($skill),
                $student->getSkills()->toArray()
            ),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'skills' => $this->skills,
        ];
    }
}
