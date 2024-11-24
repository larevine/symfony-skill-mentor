<?php

declare(strict_types=1);

namespace App\Application\DTO\Response\Teacher;

use App\Application\DTO\Response\Group\GroupResponse;
use App\Application\DTO\Response\Skill\SkillProficiencyResponse;
use App\Domain\Entity\Teacher;

readonly class TeacherResponse
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
        private int $max_groups,
        private array $groups,
        private array $skills,
    ) {
    }

    public static function fromEntity(Teacher $teacher): self
    {
        return new self(
            $teacher->getId(),
            $teacher->getFirstName(),
            $teacher->getLastName(),
            $teacher->getEmail(),
            $teacher->getMaxGroups(),
            array_map(
                fn ($group) => GroupResponse::fromEntity($group),
                $teacher->getTeachingGroups()->toArray()
            ),
            array_map(
                fn ($skill) => SkillProficiencyResponse::fromEntity($skill),
                $teacher->getSkills()->toArray()
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

    public function getMaxGroups(): int
    {
        return $this->max_groups;
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
