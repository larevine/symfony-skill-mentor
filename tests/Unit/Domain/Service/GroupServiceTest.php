<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Domain\Event\Group\GroupCreatedEvent;
use App\Domain\Event\Group\GroupUpdatedEvent;
use App\Domain\Exception\GroupException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Service\GroupService;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GroupServiceTest extends TestCase
{
    private GroupRepositoryInterface $group_repository;
    private SkillRepositoryInterface $skill_repository;
    private ProducerInterface $domain_events_producer;
    private GroupService $group_service;

    protected function setUp(): void
    {
        $this->group_repository = $this->createMock(GroupRepositoryInterface::class);
        $this->skill_repository = $this->createMock(SkillRepositoryInterface::class);
        $this->domain_events_producer = $this->createMock(ProducerInterface::class);

        $this->group_service = new GroupService(
            $this->group_repository,
            $this->skill_repository,
            $this->domain_events_producer,
        );
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    public function testCreateGroupSuccess(): void
    {
        // Arrange
        $name = 'Test Group';
        $min_students = 1;
        $max_students = 10;

        $this->group_repository->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (Group $group) {
                $this->setEntityId($group, 1);
                return $group;
            });

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === GroupCreatedEvent::NAME;
                }),
                GroupCreatedEvent::NAME
            );

        // Act
        $group = $this->group_service->create(
            $name,
            [],
            $min_students,
            $max_students
        );

        // Assert
        self::assertSame($name, $group->getName());
        self::assertSame($min_students, $group->getMinStudents());
        self::assertSame($max_students, $group->getMaxStudents());
    }

    public function testUpdateGroupSuccess(): void
    {
        // Arrange
        $group = new Group('Test Group', null, 1, 10);
        $this->setEntityId($group, 1);

        $new_name = 'Updated Group';
        $new_min_students = 2;
        $new_max_students = 15;

        $this->group_repository->expects(self::once())
            ->method('findById')
            ->with($group->getId())
            ->willReturn($group);

        $this->group_repository->expects(self::exactly(2))
            ->method('save')
            ->with($group);

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === GroupUpdatedEvent::NAME;
                }),
                GroupUpdatedEvent::NAME
            );

        // Act
        $this->group_service->update($group, $new_name, $new_min_students, $new_max_students);

        // Assert
        self::assertSame($new_name, $group->getName());
        self::assertSame($new_max_students, $group->getMaxStudents());
    }

    public function testAddStudentToFullGroup(): void
    {
        // Arrange
        $group = new Group('Test Group', null, 1, 2);
        $this->setEntityId($group, 1);

        $student1 = new Student('John', 'Doe', 'john@example.com', 'password123');
        $student2 = new Student('Jane', 'Smith', 'jane@example.com', 'password123');
        $student3 = new Student('Bob', 'Wilson', 'bob@example.com', 'password123');

        // Add max number of students
        $group->addStudent($student1);
        $group->addStudent($student2);

        $this->group_repository->expects(self::once())
            ->method('isGroupFull')
            ->with($group)
            ->willReturn(true);

        // Act & Assert
        $this->expectException(GroupException::class);
        $this->expectExceptionMessage('Group has reached maximum capacity');

        $this->group_service->addStudent($group, $student3);
    }

    public function testAssignTeacherSuccess(): void
    {
        // Arrange
        $group = new Group('Test Group', null, 1, 10);
        $this->setEntityId($group, 1);

        $teacher = new Teacher('John', 'Doe', 'john@example.com', 'password123');
        $this->setEntityId($teacher, 1);

        $this->group_repository->expects(self::once())
            ->method('findById')
            ->with($group->getId())
            ->willReturn($group);

        $this->group_repository->expects(self::exactly(2))
            ->method('save')
            ->with($group);

        // Act
        $this->group_service->assignTeacher($group, $teacher);

        // Assert
        self::assertSame($teacher, $group->getTeacher());
    }
}
