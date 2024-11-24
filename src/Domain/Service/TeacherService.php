<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Aggregate\TeacherWorkloadAggregate;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Teacher;
use App\Domain\Exception\TeacherException;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\PersonName;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\TeacherFilterRequest;
use DomainException;
use App\Domain\Service\TeacherServiceInterface;

readonly class TeacherService implements TeacherServiceInterface
{
    public function __construct(
        private TeacherRepositoryInterface $teacher_repository,
        private SkillRepositoryInterface $skill_repository,
    ) {
    }

    public function findById(EntityId $id): ?Teacher
    {
        return $this->teacher_repository->findById($id->getValue());
    }

    /**
     * @return array<Teacher>
     */
    public function findAll(): array
    {
        return $this->teacher_repository->findAll();
    }

    /**
     * @return array<Teacher>
     */
    public function findByFilter(TeacherFilterRequest $filter): array
    {
        $criteria = $this->buildFilterCriteria($filter);
        $order_by = $this->buildSortOrder($filter);
        $limit = $filter->per_page;
        $offset = ($filter->page - 1) * $filter->per_page;

        return $this->teacher_repository->findBy($criteria, $order_by, $limit, $offset);
    }

    public function countByFilter(TeacherFilterRequest $filter): int
    {
        $criteria = $this->buildFilterCriteria($filter);
        return $this->teacher_repository->count($criteria);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilterCriteria(TeacherFilterRequest $filter): array
    {
        $criteria = [];

        if ($filter->search !== null) {
            $criteria['name'] = $filter->search;
        }

        if ($filter->available_for_groups === true) {
            $criteria['available_for_groups'] = true;
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
    private function buildSortOrder(TeacherFilterRequest $filter): ?array
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

    public function create(
        string $first_name,
        string $last_name,
        string $email,
        array $roles = ['ROLE_TEACHER'],
        int $max_groups = 2,
    ): Teacher {
        try {
            $person_name = new PersonName($first_name, $last_name);
            $email_vo = new Email($email);

            $teacher = new Teacher(
                $person_name->getFirstName(),
                $person_name->getLastName(),
                $email_vo->getValue(),
                $roles,
                $max_groups
            );

            $this->teacher_repository->save($teacher);

            return $teacher;
        } catch (DomainException $e) {
            throw TeacherException::fromDomainException($e);
        }
    }

    public function assignToGroup(Teacher $teacher, Group $group): void
    {
        try {
            $workload = new TeacherWorkloadAggregate(
                new EntityId($teacher->getId()),
                $this->teacher_repository
            );
            $workload->assignGroup($group);
        } catch (DomainException $e) {
            throw TeacherException::fromDomainException($e);
        }
    }

    public function removeFromGroup(Teacher $teacher, Group $group): void
    {
        try {
            $workload = new TeacherWorkloadAggregate(
                new EntityId($teacher->getId()),
                $this->teacher_repository
            );
            $workload->removeGroup($group);
        } catch (DomainException $e) {
            throw TeacherException::fromDomainException($e);
        }
    }

    public function addSkill(Teacher $teacher, Skill $skill, ProficiencyLevel $level): void
    {
        $aggregate = new TeacherWorkloadAggregate(
            new EntityId($teacher->getId()),
            $this->teacher_repository
        );
        $aggregate->addSkill($skill, $level);
    }

    public function removeSkill(Teacher $teacher, Skill $skill): void
    {
        $aggregate = new TeacherWorkloadAggregate(
            new EntityId($teacher->getId()),
            $this->teacher_repository
        );
        $aggregate->removeSkill($skill);
    }

    public function update(
        Teacher $teacher,
        string $first_name,
        string $last_name,
        string $email,
        int $max_groups
    ): void {
        try {
            $workload = new TeacherWorkloadAggregate(
                new EntityId($teacher->getId()),
                $this->teacher_repository
            );

            $workload->updatePersonalInfo($first_name, $last_name, $email);
            $workload->updateMaxGroups($max_groups);
        } catch (DomainException $e) {
            throw TeacherException::fromDomainException($e);
        }
    }

    public function save(Teacher $teacher): void
    {
        $this->teacher_repository->save($teacher);
    }

    public function delete(Teacher $teacher): void
    {
        $this->teacher_repository->remove($teacher);
    }

    public function findSkillById(EntityId $id): ?Skill
    {
        return $this->skill_repository->findById($id->getValue());
    }
}
