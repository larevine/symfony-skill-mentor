<?php

declare(strict_types=1);

namespace App\Domain\Aggregate;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\GroupCapacity;
use App\Domain\ValueObject\GroupName;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Entity\Skill;
use App\Domain\Entity\SkillProficiency;
use DomainException;

class GroupAggregate extends AbstractAggregate
{
    private ?Group $group = null;

    public function __construct(
        EntityId $id,
        private readonly GroupRepositoryInterface $group_repository,
    ) {
        parent::__construct($id);
    }

    private function getGroup(): Group
    {
        if ($this->group === null) {
            $this->group = $this->group_repository->findById($this->id->getValue());
            if ($this->group === null) {
                throw new DomainException('Group not found');
            }
        }

        return $this->group;
    }

    public function addStudent(Student $student): void
    {
        $group = $this->getGroup();

        // Бизнес-правило: проверка максимального количества студентов в группе
        if ($group->getStudents()->count() >= $group->getMaxStudents()) {
            throw new DomainException('Group has reached maximum capacity');
        }

        $group->addStudent($student);
        $this->group_repository->save($group);
    }

    public function addRequiredSkill(Skill $skill, ProficiencyLevel $level): void
    {
        $group = $this->getGroup();

        // Check if skill already exists
        foreach ($group->getSkills() as $existing_skill) {
            if ($existing_skill->getSkill()->getId() === $skill->getId()) {
                throw new DomainException('Skill already exists in group requirements');
            }
        }

        $skillProficiency = new SkillProficiency(
            skill: $skill,
            level: $level,
            group: $group
        );

        $group->addSkill($skillProficiency);
        $this->group_repository->save($group);
    }

    public function removeStudent(Student $student): void
    {
        $group = $this->getGroup();

        if (!$group->getStudents()->contains($student)) {
            throw new DomainException('Student is not in this group');
        }

        $group->removeStudent($student);
        $this->group_repository->save($group);
    }

    public function assignTeacher(Teacher $teacher): void
    {
        $group = $this->getGroup();

        // Бизнес-правило: у группы может быть только один основной преподаватель
        if ($group->getTeacher() !== null) {
            throw new DomainException('Group already has a teacher assigned');
        }

        $group->setTeacher($teacher);
        $this->group_repository->save($group);
    }

    public function rename(string $name): void
    {
        $group = $this->getGroup();
        $group_name = new GroupName($name);
        $group->setName($group_name->getValue());
        $this->group_repository->save($group);
    }

    public function updateCapacity(int $min_students, int $max_students): void
    {
        $group = $this->getGroup();

        // Create new capacity to validate values
        $capacity = new GroupCapacity($min_students, $max_students);

        // Check if new capacity can accommodate current students
        if (!$capacity->canAcceptMoreStudents($group->getStudents()->count())) {
            throw new DomainException('New capacity is less than current number of students');
        }

        if (!$capacity->hasMinimumStudents($group->getStudents()->count())) {
            throw new DomainException('New minimum size is greater than current number of students');
        }

        // Update the group with new capacity
        $group->setCapacity($min_students, $max_students);
        $this->group_repository->save($group);
    }

    public function removeSkill(Skill $skill): void
    {
        $group = $this->getGroup();
        $found = false;

        foreach ($group->getRequiredSkills() as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                $group->removeSkill($skill_proficiency);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new DomainException('Group does not require this skill');
        }

        $this->group_repository->save($group);
    }
}
