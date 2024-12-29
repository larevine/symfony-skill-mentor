<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Aggregate\GroupAggregate;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Domain\Event\Group\GroupCreatedEvent;
use App\Domain\Event\Group\GroupDeletedEvent;
use App\Domain\Event\Group\GroupSkillAddedEvent;
use App\Domain\Event\Group\GroupSkillRemovedEvent;
use App\Domain\Event\Group\GroupStudentAddedEvent;
use App\Domain\Event\Group\GroupStudentRemovedEvent;
use App\Domain\Event\Group\GroupTeacherAssignedEvent;
use App\Domain\Event\Group\GroupUpdatedEvent;
use App\Domain\Exception\GroupException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\GroupCapacity;
use App\Domain\ValueObject\GroupName;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\GroupFilterRequest;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

readonly class GroupService implements GroupServiceInterface
{
    public function __construct(
        private GroupRepositoryInterface $group_repository,
        private StudentRepositoryInterface $student_repository,
        private TeacherRepositoryInterface $teacher_repository,
        private SkillRepositoryInterface $skill_repository,
        private ProducerInterface $domain_events_producer,
    ) {
    }

    public function findById(EntityId $id): ?Group
    {
        return $this->group_repository->findById($id->getValue());
    }

    public function findSkillById(EntityId $id): ?Skill
    {
        return $this->skill_repository->findById($id->getValue());
    }

    public function findTeacherById(EntityId $id): ?Teacher
    {
        return $this->teacher_repository->findById($id->getValue());
    }

    public function findStudentById(EntityId $id): ?Student
    {
        return $this->student_repository->findById($id->getValue());
    }

    public function hasAvailableSlots(Group $group): bool
    {
        return count($group->getStudents()) < $group->getMaxStudents();
    }

    public function findSuitableGroups(Student $student): array
    {
        $all_groups = $this->group_repository->findAll();

        return array_filter($all_groups, function (Group $group) use ($student) {
            if (!$this->hasAvailableSlots($group)) {
                return false;
            }

            if ($student->getGroups()->contains($group)) {
                return false;
            }

            return $this->hasRequiredSkills($student, $group);
        });
    }

    /**
     * @return array<Group>
     */
    public function findAll(): array
    {
        return $this->group_repository->findAll();
    }

    /**
     * @return array<Group>
     */
    public function findByFilter(GroupFilterRequest $filter): array
    {
        $criteria = $this->buildFilterCriteria($filter);
        $order_by = $this->buildSortOrder($filter);
        $limit = $filter->per_page;
        $offset = ($filter->page - 1) * $filter->per_page;

        return $this->group_repository->findBy($criteria, $order_by, $limit, $offset);
    }

    public function countByFilter(GroupFilterRequest $filter): int
    {
        $criteria = $this->buildFilterCriteria($filter);
        return $this->group_repository->count($criteria);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilterCriteria(GroupFilterRequest $filter): array
    {
        $criteria = [];

        if ($filter->search !== null) {
            $criteria['name'] = $filter->search;
        }

        if (!empty($filter->teacher_ids)) {
            $criteria['teacher_ids'] = $filter->teacher_ids;
        }

        if (!empty($filter->required_skill_ids)) {
            $criteria['required_skill_ids'] = $filter->required_skill_ids;
        }

        if ($filter->has_available_spots === true) {
            $criteria['has_available_spots'] = true;
        }

        return $criteria;
    }

    /**
     * @return array<string, string>|null
     */
    private function buildSortOrder(GroupFilterRequest $filter): ?array
    {
        if ($filter->sort_by === null) {
            return null;
        }

        $order_by = [];
        foreach ($filter->sort_by as $field) {
            $order_by[$field] = $filter->sort_order;
        }

        return $order_by;
    }

    public function create(
        string $name,
        array $students,
        int $min_students,
        int $max_students,
        ?Teacher $teacher = null,
    ): Group {
        return $this->wrapDomainException(function () use ($name, $students, $min_students, $max_students, $teacher) {
            $group_name = new GroupName($name);
            $capacity = new GroupCapacity($min_students, $max_students);

            $group = new Group(
                $group_name->getValue(),
                $teacher,
                $capacity->getMinStudents(),
                $capacity->getMaxStudents()
            );

            $this->group_repository->save($group);

            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );

            foreach ($students as $student) {
                $group_aggregate->addStudent($student);

                $event = new GroupStudentAddedEvent(
                    $group->getId(),
                    $student->getId()
                );
                $this->domain_events_producer->publish(
                    json_encode($event->toArray()),
                    $event->getEventName()
                );
            }

            $event = new GroupCreatedEvent(
                $group->getId(),
                [
                    'name' => $name,
                    'min_students' => $min_students,
                    'max_students' => $max_students,
                    'teacher_id' => $teacher?->getId(),
                    'students' => array_map(fn (Student $student) => $student->getId(), $students),
                ]
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );

            return $group;
        });
    }

    public function update(Group $group, ?string $name = null, ?int $max_students = null): void
    {
        $this->wrapDomainException(function () use ($group, $name, $max_students) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );

            if ($name !== null) {
                $group_name = new GroupName($name);
                $group_aggregate->rename($group_name->getValue());
            }

            if ($max_students !== null) {
                $capacity = new GroupCapacity($group->getMinStudents(), $max_students);
                $group_aggregate->updateCapacity($capacity->getMinStudents(), $capacity->getMaxStudents());
            }

            $event = new GroupUpdatedEvent(
                $group->getId(),
                [
                    'name' => $name ?? $group->getName(),
                    'min_students' => $group->getMinStudents(),
                    'max_students' => $max_students ?? $group->getMaxStudents(),
                    'students' => array_map(fn (Student $student) => $student->getId(), $group->getStudents()),
                ]
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function delete(Group $group): void
    {
        $this->wrapDomainException(function () use ($group) {
            $this->group_repository->remove($group);

            $event = new GroupDeletedEvent($group->getId());
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function addStudent(Group $group, Student $student): void
    {
        $this->wrapDomainException(function () use ($group, $student) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->addStudent($student);

            $event = new GroupStudentAddedEvent(
                $group->getId(),
                $student->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function removeStudent(Group $group, Student $student): void
    {
        $this->wrapDomainException(function () use ($group, $student) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->removeStudent($student);

            $event = new GroupStudentRemovedEvent(
                $group->getId(),
                $student->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function assignTeacher(Group $group, Teacher $teacher): void
    {
        $this->wrapDomainException(function () use ($group, $teacher) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->assignTeacher($teacher);

            $event = new GroupTeacherAssignedEvent(
                $group->getId(),
                $teacher->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function addSkill(Group $group, Skill $skill, ProficiencyLevel $level): void
    {
        $this->wrapDomainException(function () use ($group, $skill, $level) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->addRequiredSkill($skill, $level);

            $event = new GroupSkillAddedEvent(
                $group->getId(),
                $skill->getId(),
                $level->getLabel()
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function removeSkill(Group $group, Skill $skill): void
    {
        $this->wrapDomainException(function () use ($group, $skill) {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->removeSkill($skill);

            $event = new GroupSkillRemovedEvent(
                $group->getId(),
                $skill->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event->toArray()),
                $event->getEventName()
            );
        });
    }

    public function hasRequiredSkills(Student $student, Group $group): bool
    {
        $student_skills = $student->getSkills();
        $required_skills = $group->getSkills();

        foreach ($required_skills as $required_skill) {
            $has_skill = false;
            $required_level = $required_skill->getLevel();

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

    public function save(Group $group): void
    {
        $this->group_repository->save($group);
    }

    private function wrapDomainException(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (DomainException $e) {
            throw GroupException::fromDomainException($e);
        }
    }
}
