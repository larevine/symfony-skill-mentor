<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\GroupFilterRequest;

interface GroupServiceInterface
{
    /**
     * Finds a group by its ID.
     * @see GroupService::findById()
     */
    public function findById(EntityId $id): ?Group;

    /**
     * Finds a skill by its ID.
     * @see GroupService::findSkillById()
     */
    public function findSkillById(EntityId $id): ?Skill;

    /**
     * Checks if a group has available slots for new students.
     * @see GroupService::hasAvailableSlots()
     */
    public function hasAvailableSlots(Group $group): bool;

    /**
     * Finds suitable groups for a student based on available slots and required skills.
     * @return array<Group>
     * @see GroupService::findSuitableGroups()
     */
    public function findSuitableGroups(Student $student): array;

    /**
     * Returns all groups.
     * @return array<Group>
     * @see GroupService::findAll()
     */
    public function findAll(): array;

    /**
     * Finds groups based on filter criteria.
     * @return array<Group>
     * @see GroupService::findByFilter()
     */
    public function findByFilter(GroupFilterRequest $filter): array;

    /**
     * Counts groups matching filter criteria.
     * @see GroupService::countByFilter()
     */
    public function countByFilter(GroupFilterRequest $filter): int;

    /**
     * Creates a new group.
     * @param array<Student> $students Initial students in the group
     * @see GroupService::create()
     */
    public function create(
        string $name,
        array $students,
        int $min_students,
        int $max_students,
        ?Teacher $teacher = null,
    ): Group;

    /**
     * Updates an existing group.
     * @see GroupService::update()
     */
    public function update(Group $group, ?string $name = null, ?int $max_students = null): void;

    /**
     * Adds a student to a group.
     * @see GroupService::addStudent()
     */
    public function addStudent(Group $group, Student $student): void;

    /**
     * Removes a student from a group.
     * @see GroupService::removeStudent()
     */
    public function removeStudent(Group $group, Student $student): void;

    /**
     * Adds a skill to a group with specified proficiency level.
     * @see GroupService::addSkill()
     */
    public function addSkill(Group $group, Skill $skill, ProficiencyLevel $level): void;

    /**
     * Removes a skill from a group.
     * @see GroupService::removeSkill()
     */
    public function removeSkill(Group $group, Skill $skill): void;

    /**
     * Deletes a group.
     * @see GroupService::delete()
     */
    public function delete(Group $group): void;
}
