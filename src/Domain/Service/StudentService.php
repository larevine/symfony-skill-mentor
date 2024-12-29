<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Aggregate\GroupAggregate;
use App\Domain\Aggregate\StudentSkillsAggregate;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Student;
use App\Domain\Event\Student\StudentCreatedEvent;
use App\Domain\Event\Student\StudentDeletedEvent;
use App\Domain\Event\Student\StudentJoinedGroupEvent;
use App\Domain\Event\Student\StudentLeftGroupEvent;
use App\Domain\Event\Student\StudentSkillAddedEvent;
use App\Domain\Event\Student\StudentSkillRemovedEvent;
use App\Domain\Event\Student\StudentUpdatedEvent;
use App\Domain\Exception\StudentException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\Filter\StudentFilterRequest;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

readonly class StudentService implements StudentServiceInterface
{
    public function __construct(
        private StudentRepositoryInterface $student_repository,
        private SkillRepositoryInterface $skill_repository,
        private GroupRepositoryInterface $group_repository,
        private ProducerInterface $domain_events_producer,
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
        return $this->student_repository->findByFilter($filter);
    }

    public function joinGroup(Student $student, Group $group): void
    {
        try {
            $group_aggregate = new GroupAggregate(
                new EntityId($group->getId()),
                $this->group_repository
            );
            $group_aggregate->addStudent($student);

            $event = new StudentJoinedGroupEvent(
                $student->getId(),
                $group->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event),
                $event->getEventName()
            );
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

            $event = new StudentLeftGroupEvent(
                $student->getId(),
                $group->getId()
            );
            $this->domain_events_producer->publish(
                json_encode($event),
                $event->getEventName()
            );
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

        $event = new StudentSkillAddedEvent(
            $student->getId(),
            $skill->getId(),
            $level->getLabel()
        );
        $this->domain_events_producer->publish(
            json_encode($event),
            $event->getEventName()
        );
    }

    public function removeSkill(Student $student, Skill $skill): void
    {
        $aggregate = new StudentSkillsAggregate(
            new EntityId($student->getId()),
            $this->student_repository
        );
        $aggregate->removeSkill($skill);

        $event = new StudentSkillRemovedEvent(
            $student->getId(),
            $skill->getId()
        );
        $this->domain_events_producer->publish(
            json_encode($event),
            $event->getEventName()
        );
    }

    public function updateSkillLevel(Student $student, Skill $skill, ProficiencyLevel $level): void
    {
        try {
            $student_aggregate = new StudentSkillsAggregate(
                new EntityId($student->getId()),
                $this->student_repository
            );
            $student_aggregate->updateSkillLevel($skill, $level);

            $event = new StudentSkillAddedEvent(
                $student->getId(),
                $skill->getId(),
                $level->getLabel()
            );
            $this->domain_events_producer->publish(
                json_encode($event),
                $event->getEventName()
            );
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function create(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        array $initial_skills = [],
    ): Student {
        try {
            $existing_student = $this->student_repository->findOneByEmail($email);
            if ($existing_student !== null) {
                throw new DomainException("Student with email {$email} already exists");
            }

            // Create student
            $student = new Student(
                $first_name,
                $last_name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                ['ROLE_STUDENT']
            );

            // Add initial skills if provided
            if (!empty($initial_skills)) {
                foreach ($initial_skills as $skill_data) {
                    $skill = $this->findSkillById(new EntityId($skill_data['skill_id']));
                    if ($skill === null) {
                        throw new DomainException(sprintf('Skill with ID %d not found', $skill_data['skill_id']));
                    }

                    $student->addSkill($skill_data['skill'], $skill_data['level']);
                }
            }

            // Save student with all skills
            $this->student_repository->save($student);

            // Publish events
            if (!empty($initial_skills)) {
                foreach ($initial_skills as $skill_data) {
                    $event = new StudentSkillAddedEvent(
                        $student->getId(),
                        $skill_data['skill']->getId(),
                        $skill_data['level']->getLabel()
                    );
                    $this->domain_events_producer->publish(
                        json_encode($event),
                        $event->getEventName()
                    );
                }
            }

            $event = new StudentCreatedEvent(
                $student->getId(),
                [
                    'first_name' => $student->getFirstName(),
                    'last_name' => $student->getLastName(),
                    'email' => $student->getEmail(),
                ]
            );
            $this->domain_events_producer->publish(
                json_encode($event),
                $event->getEventName()
            );

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

            $event = new StudentUpdatedEvent(
                $student->getId(),
                [
                    'first_name' => $student->getFirstName(),
                    'last_name' => $student->getLastName(),
                    'email' => $student->getEmail(),
                ]
            );
            $this->domain_events_producer->publish(
                json_encode($event),
                $event->getEventName()
            );
        } catch (DomainException $e) {
            throw StudentException::fromDomainException($e);
        }
    }

    public function delete(Student $student): void
    {
        $this->student_repository->remove($student);

        $event = new StudentDeletedEvent($student->getId());
        $this->domain_events_producer->publish(
            json_encode($event),
            $event->getEventName()
        );
    }
}
