<?php

declare(strict_types=1);

namespace App\Domain\Aggregate;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Entity\Skill;
use App\Domain\Entity\SkillProficiency;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use App\Domain\ValueObject\ProficiencyLevel;
use DomainException;
use Doctrine\Common\Collections\Collection;

class StudentSkillsAggregate extends AbstractAggregate
{
    private ?Student $student = null;

    public function __construct(
        EntityId $id,
        private readonly StudentRepositoryInterface $student_repository,
    ) {
        parent::__construct($id);
    }

    private function getStudent(): Student
    {
        if ($this->student === null) {
            $this->student = $this->student_repository->findById($this->id->getValue());
            if ($this->student === null) {
                throw new DomainException('Student not found');
            }
        }

        return $this->student;
    }

    public function addSkill(Skill $skill, ProficiencyLevel $level): void
    {
        $student = $this->getStudent();

        // Check if student already has this skill
        foreach ($student->getSkills() as $existing_skill) {
            if ($existing_skill->getSkill() === $skill) {
                throw new DomainException('Student already has this skill');
            }
        }

        $proficiency = new SkillProficiency(
            skill: $skill,
            level: $level,
            student: $student
        );

        $student->addSkill($proficiency);
        $this->student_repository->save($student);
    }

    public function removeSkill(Skill $skill): void
    {
        $student = $this->getStudent();
        $found = false;

        foreach ($student->getSkills() as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                $student->removeSkill($skill_proficiency);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new DomainException('Student does not have this skill');
        }

        $this->student_repository->save($student);
    }

    public function updateSkillLevel(Skill $skill, ProficiencyLevel $newLevel): void
    {
        $student = $this->getStudent();
        $found = false;

        foreach ($student->getSkills() as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                $skill_proficiency = new SkillProficiency($skill, $newLevel);
                $skill_proficiency->setStudent($student);
                $student->removeSkill($skill_proficiency);
                $student->addSkill($skill_proficiency);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new DomainException('Student does not have this skill');
        }

        $this->student_repository->save($student);
    }

    public function getSkillLevel(Skill $skill): ?ProficiencyLevel
    {
        $student = $this->getStudent();

        foreach ($student->getSkills() as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                return $skill_proficiency->getLevel();
            }
        }

        return null;
    }

    public function hasRequiredSkills(Group $group): bool
    {
        $student = $this->getStudent();
        $student_skills = $student->getSkills();

        // Get all required skills for the group
        $required_skills = $group->getSkills();

        foreach ($required_skills as $required_skill) {
            $has_skill = false;
            $required_level = $required_skill->getLevel();

            // Check if student has the required skill at sufficient level
            foreach ($student_skills as $student_skill) {
                if ($student_skill->getSkill()->getId() === $required_skill->getSkill()->getId()) {
                    if ($student_skill->getLevel()->getValue() >= $required_level->getValue()) {
                        $has_skill = true;
                        break;
                    }
                }
            }

            if (!$has_skill) {
                return false;
            }
        }

        return true;
    }

    public function joinGroup(Group $group): void
    {
        $student = $this->getStudent();

        if ($student->getGroups()->contains($group)) {
            throw new DomainException('Student is already in this group');
        }

        if (!$this->hasRequiredSkills($group)) {
            throw new DomainException('Student does not have required skills for this group');
        }

        if (!$group->canAcceptMoreStudents()) {
            throw new DomainException('Group has reached maximum capacity');
        }

        $student->addGroup($group);
        $this->student_repository->save($student);
    }

    public function leaveGroup(Group $group): void
    {
        $student = $this->getStudent();

        if (!$student->getGroups()->contains($group)) {
            throw new DomainException('Student is not in this group');
        }

        $student->removeGroup($group);
        $this->student_repository->save($student);
    }

    public function getGroups(): Collection
    {
        $student = $this->getStudent();
        return $student->getGroups();
    }

    public function getSkills(): Collection
    {
        $student = $this->getStudent();
        return $student->getSkills();
    }

    public function updatePersonalInfo(string $first_name, string $last_name, string $email): void
    {
        $student = $this->getStudent();

        // Create value objects to validate input
        $person_name = new PersonName($first_name, $last_name);
        $email_vo = new Email($email);

        // Update student info using parent class methods
        $student->updateName($person_name);
        $student->updateEmail($email_vo);

        $this->student_repository->save($student);
    }
}
