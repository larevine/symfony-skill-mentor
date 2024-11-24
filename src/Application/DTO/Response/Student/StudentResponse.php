<?php

declare(strict_types=1);

namespace App\Application\DTO\Response\Student;

use App\Application\DTO\Response\Group\GroupResponse;
use App\Application\DTO\Response\Skill\SkillProficiencyResponse;
use App\Domain\Entity\Student;

readonly class StudentResponse
{
    /**
     * @param array<GroupResponse> $groups
     * @param array<SkillProficiencyResponse> $skills
     */
    public function __construct(
        private int $id,
        private string $first_name,
        private string $last_name,
        private string $email,
        private array $groups,
        private array $skills,
    ) {
    }

    public static function fromEntity(Student $student): self
    {
        return new self(
            $student->getId(),
            $student->getFirstName(),
            $student->getLastName(),
            $student->getEmail(),
            array_map(
                fn ($group) => GroupResponse::fromEntity($group),
                $student->getGroups()->toArray()
            ),
            array_map(
                fn ($skill) => SkillProficiencyResponse::fromEntity($skill),
                $student->getSkills()->toArray()
            ),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /** @return array<GroupResponse> */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /** @return array<SkillProficiencyResponse> */
    public function getSkills(): array
    {
        return $this->skills;
    }
}
