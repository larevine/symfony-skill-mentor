<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Group;
use App\Domain\Entity\SkillProficiency;

readonly class GroupResponse
{
    /**
     * @param array<StudentResponse> $students
     * @param array<SkillResponse> $required_skills
     */
    public function __construct(
        public int $id,
        public string $name,
        public TeacherResponse $teacher,
        public int $max_students,
        public array $students = [],
        public array $required_skills = [],
    ) {
    }

    public static function fromEntity(Group $group): self
    {
        return new self(
            id: $group->getId() ?? 0,
            name: $group->getName(),
            teacher: TeacherResponse::fromEntity($group->getTeacher(), false),
            max_students: $group->getMaxStudents(),
            students: array_map(
                static fn ($student) => StudentResponse::fromEntity($student),
                $group->getStudents()->toArray()
            ),
            required_skills: array_map(
                static fn (SkillProficiency $skill) => SkillResponse::fromSkillProficiency($skill),
                $group->getRequiredSkills()->toArray()
            ),
        );
    }
}
