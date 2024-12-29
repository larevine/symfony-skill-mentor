<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Group;
use App\Domain\Entity\SkillProficiency;
use App\Domain\Entity\Teacher;
use JsonSerializable;

readonly class TeacherResponse implements JsonSerializable
{
    /**
     * @param array<GroupResponse> $groups
     * @param array<SkillResponse> $skills
     */
    public function __construct(
        public ?int $id,
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
            first_name: $teacher->getFirstName(),
            last_name: $teacher->getLastName(),
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

    public function getValue(): ?int
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'max_groups' => $this->max_groups,
            'groups' => $this->groups,
            'skills' => $this->skills,
        ];
    }
}
