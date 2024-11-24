<?php

declare(strict_types=1);

namespace App\Domain\Aggregate;

use App\Domain\Entity\Teacher;
use App\Domain\Entity\Group;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\PersonName;
use App\Domain\ValueObject\Email;
use App\Domain\Entity\Skill;
use App\Domain\Entity\SkillProficiency;
use App\Domain\ValueObject\ProficiencyLevel;
use DomainException;

class TeacherWorkloadAggregate extends AbstractAggregate
{
    private ?Teacher $teacher = null;

    public function __construct(
        EntityId $id,
        private readonly TeacherRepositoryInterface $teacher_repository,
    ) {
        parent::__construct($id);
    }

    private function getTeacher(): Teacher
    {
        if ($this->teacher === null) {
            $this->teacher = $this->teacher_repository->findById($this->id->getValue());
            if ($this->teacher === null) {
                throw new DomainException('Teacher not found');
            }
        }

        return $this->teacher;
    }

    public function assignGroup(Group $group): void
    {
        $teacher = $this->getTeacher();

        // Проверяем, не превышен ли лимит групп у преподавателя
        if ($teacher->getTeachingGroups()->count() >= $teacher->getMaxGroups()) {
            throw new DomainException(
                sprintf(
                    'Teacher has reached maximum number of groups (%d)',
                    $teacher->getMaxGroups()
                )
            );
        }

        // Проверяем, не назначен ли уже преподаватель этой группе
        if ($group->getTeacher() !== null) {
            throw new DomainException('Group already has a teacher assigned');
        }

        $teacher->addGroup($group);
        $this->teacher_repository->save($teacher);
    }

    public function removeGroup(Group $group): void
    {
        $teacher = $this->getTeacher();

        if (!$teacher->getTeachingGroups()->contains($group)) {
            throw new DomainException('Teacher is not assigned to this group');
        }

        $teacher->removeGroup($group);
        $this->teacher_repository->save($teacher);
    }

    public function updateMaxGroups(int $newMaxGroups): void
    {
        $teacher = $this->getTeacher();

        if ($newMaxGroups < $teacher->getTeachingGroups()->count()) {
            throw new DomainException(
                sprintf(
                    'Cannot set max groups to %d as teacher already has %d groups',
                    $newMaxGroups,
                    $teacher->getTeachingGroups()->count()
                )
            );
        }

        $teacher->setMaxGroups($newMaxGroups);
        $this->teacher_repository->save($teacher);
    }

    public function getCurrentWorkload(): int
    {
        $teacher = $this->getTeacher();
        return $teacher->getTeachingGroups()->count();
    }

    public function hasAvailableCapacity(): bool
    {
        $teacher = $this->getTeacher();
        return $teacher->getTeachingGroups()->count() < $teacher->getMaxGroups();
    }

    public function getRoot(): Teacher
    {
        return $this->getTeacher();
    }

    public function updatePersonalInfo(string $first_name, string $last_name, string $email): void
    {
        $teacher = $this->getTeacher();

        // Create value objects to validate input
        $person_name = new PersonName($first_name, $last_name);
        $email_vo = new Email($email);

        // Update teacher info
        $teacher->updateName($person_name);
        $teacher->updateEmail($email_vo);

        $this->teacher_repository->save($teacher);
    }

    public function addSkill(Skill $skill, ProficiencyLevel $level): void
    {
        $teacher = $this->getTeacher();

        // Check if teacher already has this skill
        foreach ($teacher->getSkills() as $existing_skill) {
            if ($existing_skill->getSkill() === $skill) {
                throw new DomainException('Teacher already has this skill');
            }
        }

        $proficiency = new SkillProficiency(
            skill: $skill,
            level: $level,
            teacher: $teacher
        );

        $teacher->addSkill($proficiency);
        $this->teacher_repository->save($teacher);
    }

    public function removeSkill(Skill $skill): void
    {
        $teacher = $this->getTeacher();
        $found = false;

        foreach ($teacher->getSkills() as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                $teacher->removeSkill($skill_proficiency);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new DomainException('Teacher does not have this skill');
        }

        $this->teacher_repository->save($teacher);
    }
}
