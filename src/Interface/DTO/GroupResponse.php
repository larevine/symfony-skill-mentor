<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Group;
use App\Domain\Entity\SkillProficiency;
use JsonSerializable;

readonly class GroupResponse implements JsonSerializable
{
    /**
     * @param array<StudentResponse> $students
     * @param array<SkillResponse> $required_skills
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public ?TeacherResponse $teacher,
        public int $max_students,
        public int $min_students,
        public array $students = [],
        public array $required_skills = [],
    ) {
    }

    public static function fromEntity(Group $group): self
    {
        return new self(
            id: $group->getId(),
            name: $group->getName(),
            teacher: $group->getTeacher() ? TeacherResponse::fromEntity($group->getTeacher(), false) : null,
            max_students: $group->getMaxStudents(),
            min_students: $group->getMinStudents(),
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'teacher' => $this->teacher,
            'max_students' => $this->max_students,
            'min_students' => $this->min_students,
            'students' => $this->students,
            'required_skills' => $this->required_skills,
        ];
    }
}
