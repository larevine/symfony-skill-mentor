<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Aggregate\GroupAggregate;
use App\Domain\Aggregate\StudentSkillsAggregate;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Student;
use App\Domain\Exception\StudentException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\StudentFilterRequest;
use DomainException;
use App\Domain\Service\StudentServiceInterface;

readonly class StudentService implements StudentServiceInterface
{
    public function __construct(
        private StudentRepositoryInterface $student_repository,
        private SkillRepositoryInterface $skill_repository,
        private GroupRepositoryInterface $group_repository,
    ) {
    }

    public function findById(EntityId $id): ?Student
    {
        return $this->student_repository->findById($id->getValue());
    }

    public function findSkillById(EntityId $id): ?Skill
    {
        return $this->skill_repository->findById($id->getValue());
    }

    /**
     * @return Student[]
     */
    public function findByFilter(StudentFilterRequest $filter): array
    {
        $criteria = $this->buildFilterCriteria($filter);
        $order_by = $this->buildSortOrder($filter);
        $limit = $filter->per_page;
        $offset = ($filter->page - 1) * $filter->per_page;

        return $this->student_repository->findBy($criteria, $order_by, $limit, $offset);
    }

    public function countByFilter(StudentFilterRequest $filter): int
    {
        $criteria = $this->buildFilterCriteria($filter);
        return $this->student_repository->count($criteria);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilterCriteria(StudentFilterRequest $filter): array
    {
        $criteria = [];

        if ($filter->search !== null) {
            $criteria['name'] = $filter->search;
        }

        if ($filter->skill_ids !== null) {
            $criteria['skill_id'] = $filter->skill_ids;
        }

        if ($filter->group_ids !== null) {
            $criteria['group_id'] = $filter->group_ids;
        }

        return $criteria;
    }

    /**
     * @return array<string, string>|null
     */
    private function buildSortOrder(StudentFilterRequest $filter): ?array
    {
        if (!isset($filter->sort_by)) {
            return null;
        }

        $order_by = [];
        foreach ($filter->sort_by as $field) {
            $order_by[$field] = $filter->sort_order ?? 'asc';
        }

        return $order_by;
    }

    public function joinGroup(Student $student, Group $group): void
    {
        try {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->addStudent($student);
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function leaveGroup(Student $student, Group $group): void
    {
        if (!$student->getGroups()->contains($group)) {
            throw StudentException::notInGroup($student, $group);
        }

        try {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->removeStudent($student);
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function addSkill(Student $student, Skill $skill, ProficiencyLevel $level): void
    {
        $aggregate = new StudentSkillsAggregate(
            new EntityId($student->getId()),
            $this->student_repository
        );
        $aggregate->addSkill($skill, $level);
    }

    public function removeSkill(Student $student, Skill $skill): void
    {
        $aggregate = new StudentSkillsAggregate(
            new EntityId($student->getId()),
            $this->student_repository
        );
        $aggregate->removeSkill($skill);
    }

    public function updateSkillLevel(Student $student, Skill $skill, ProficiencyLevel $level): void
    {
        try {
            $student_aggregate = new StudentSkillsAggregate(
                new EntityId($student->getId()),
                $this->student_repository
            );
            $student_aggregate->updateSkillLevel($skill, $level);
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function create(
        string $first_name,
        string $last_name,
        string $email,
        array $initial_skills = [],
    ): Student {
        try {
            // Create student
            $student = new Student($first_name, $last_name, $email);

            // Save first to get ID
            $this->student_repository->save($student);

            // Add initial skills if provided
            if (!empty($initial_skills)) {
                $student_aggregate = new StudentSkillsAggregate(
                    new EntityId($student->getId()),
                    $this->student_repository
                );

                foreach ($initial_skills as $skill_data) {
                    $skill = $this->findSkillById(new EntityId($skill_data['skill_id']));
                    if ($skill === null) {
                        throw new DomainException(sprintf('Skill with ID %d not found', $skill_data['skill_id']));
                    }

                    $student_aggregate->addSkill($skill, $skill_data['level']);
                }
            }

            return $student;
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function update(
        Student $student,
        string $first_name,
        string $last_name,
        string $email,
    ): void {
        try {
            $student_aggregate = new StudentSkillsAggregate(
                new EntityId($student->getId()),
                $this->student_repository
            );

            // Update personal info through aggregate
            $student_aggregate->updatePersonalInfo($first_name, $last_name, $email);
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function delete(Student $student): void
    {
        $this->student_repository->remove($student);
    }
}
