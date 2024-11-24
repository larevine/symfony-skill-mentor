<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Group;
use App\Domain\Entity\SkillProficiency;
use App\Domain\Entity\Teacher;

readonly class TeacherResponse
{
    /**
     * @param array<GroupResponse> $groups
     * @param array<SkillResponse> $skills
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $first_name,
        public string $last_name,
        public int $max_groups,
        public array $groups,
        public array $skills,
    ) {
    }

    public static function fromEntity(Teacher $teacher, bool $include_groups = true): self
    {
        return new self(
            id: $teacher->getId(),
            email: $teacher->getEmail(),
            first_name: $teacher->getName()->getFirstName(),
            last_name: $teacher->getName()->getLastName(),
            max_groups: $teacher->getMaxGroups(),
            groups: $include_groups ? array_map(
                static fn (Group $group): GroupResponse => GroupResponse::fromEntity($group),
                $teacher->getTeachingGroups()->toArray()
            ) : [],
            skills: array_map(
                static fn (SkillProficiency $skill): SkillResponse => SkillResponse::fromSkillProficiency($skill),
                $teacher->getSkills()->toArray()
            ),
        );
    }

    public function getValue(): int
    {
        return $this->id;
    }
}
