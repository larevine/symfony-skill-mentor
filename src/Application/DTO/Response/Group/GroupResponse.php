<?php

declare(strict_types=1);

namespace App\Application\DTO\Response\Group;

use App\Application\DTO\Response\Skill\SkillProficiencyResponse;
use App\Application\DTO\Response\Student\StudentResponse;
use App\Application\DTO\Response\Teacher\TeacherResponse;
use App\Domain\Entity\Group;

readonly class GroupResponse
{
    /**
     * @param array<StudentResponse> $students
     * @param array<SkillProficiencyResponse> $required_skills
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
            $group->getId(),
            $group->getName(),
            TeacherResponse::fromEntity($group->getTeacher()),
            $group->getMaxStudents(),
            array_map(
                fn ($student) => StudentResponse::fromEntity($student),
                $group->getStudents()->toArray()
            ),
            array_map(
                fn ($skill) => SkillProficiencyResponse::fromEntity($skill),
                $group->getRequiredSkills()->toArray()
            ),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTeacher(): TeacherResponse
    {
        return $this->teacher;
    }

    public function getMaxStudents(): int
    {
        return $this->max_students;
    }

    /** @return array<StudentResponse> */
    public function getStudents(): array
    {
        return $this->students;
    }

    /** @return array<SkillProficiencyResponse> */
    public function getRequiredSkills(): array
    {
        return $this->required_skills;
    }
}
