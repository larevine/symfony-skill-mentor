<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Teacher;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\TeacherFilterRequest;

interface TeacherServiceInterface
{
    /**
     * Finds a teacher by their ID.
     * @see TeacherService::findById()
     */
    public function findById(EntityId $id): ?Teacher;

    /**
     * Returns all teachers.
     * @return array<Teacher>
     * @see TeacherService::findAll()
     */
    public function findAll(): array;

    /**
     * Finds teachers based on filter criteria.
     * @return array<Teacher>
     * @see TeacherService::findByFilter()
     */
    public function findByFilter(TeacherFilterRequest $filter): array;

    /**
     * Counts teachers matching filter criteria.
     * @see TeacherService::countByFilter()
     */
    public function countByFilter(TeacherFilterRequest $filter): int;

    /**
     * Creates a new teacher.
     * @param array<string> $roles
     * @see TeacherService::create()
     */
    public function create(
        string $first_name,
        string $last_name,
        string $email,
        array $roles = ['ROLE_TEACHER'],
        int $max_groups = 2,
    ): Teacher;

    /**
     * Assigns a teacher to a group.
     * @see TeacherService::assignToGroup()
     */
    public function assignToGroup(Teacher $teacher, Group $group): void;

    /**
     * Removes a teacher from a group.
     * @see TeacherService::removeFromGroup()
     */
    public function removeFromGroup(Teacher $teacher, Group $group): void;

    /**
     * Adds a skill to a teacher with specified proficiency level.
     * @see TeacherService::addSkill()
     */
    public function addSkill(Teacher $teacher, Skill $skill, ProficiencyLevel $level): void;

    /**
     * Sends teacher skills to message queue for async processing.
     */
    public function publishTeacherSkills(Teacher $teacher): void;

    /**
     * Removes a skill from a teacher.
     * @see TeacherService::removeSkill()
     */
    public function removeSkill(Teacher $teacher, Skill $skill): void;

    /**
     * Updates teacher information.
     * @see TeacherService::update()
     */
    public function update(
        Teacher $teacher,
        string $first_name,
        string $last_name,
        string $email,
        int $max_groups
    ): void;

    /**
     * Finds a skill by its ID.
     * @see TeacherService::findSkillById()
     */
    public function findSkillById(EntityId $id): ?Skill;
}
