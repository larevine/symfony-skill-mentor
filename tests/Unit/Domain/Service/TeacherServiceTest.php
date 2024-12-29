<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Entity\Teacher;
use App\Domain\Event\Teacher\TeacherCreatedEvent;
use App\Domain\Event\Teacher\TeacherUpdatedEvent;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Domain\Service\TeacherService;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TeacherServiceTest extends TestCase
{
    private TeacherRepositoryInterface $teacher_repository;
    private SkillRepositoryInterface $skill_repository;
    private ProducerInterface $teacher_skills_producer;
    private ProducerInterface $domain_events_producer;
    private TeacherService $teacher_service;

    protected function setUp(): void
    {
        $this->teacher_repository = $this->createMock(TeacherRepositoryInterface::class);
        $this->skill_repository = $this->createMock(SkillRepositoryInterface::class);
        $this->teacher_skills_producer = $this->createMock(ProducerInterface::class);
        $this->domain_events_producer = $this->createMock(ProducerInterface::class);

        $this->teacher_service = new TeacherService(
            $this->teacher_repository,
            $this->skill_repository,
            $this->teacher_skills_producer,
            $this->domain_events_producer,
        );
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    public function testCreateTeacherSuccess(): void
    {
        // Arrange
        $first_name = 'John';
        $last_name = 'Smith';
        $email = 'john.smith@example.com';
        $roles = ['ROLE_TEACHER', 'ROLE_USER'];
        $max_groups = 2;
        $password = 'password123';

        $this->teacher_repository->expects(self::once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn(null);

        $this->teacher_repository->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (Teacher $teacher) use ($first_name, $last_name, $email) {
                $this->setEntityId($teacher, 1);
                return $teacher;
            });

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === TeacherCreatedEvent::NAME;
                }),
                TeacherCreatedEvent::NAME
            );

        // Act
        $teacher = $this->teacher_service->create(
            $first_name,
            $last_name,
            $email,
            $password,
            $roles,
            $max_groups
        );

        // Assert
        self::assertSame($first_name, $teacher->getFirstName());
        self::assertSame($last_name, $teacher->getLastName());
        self::assertSame($email, $teacher->getEmail());
        self::assertSame($roles, $teacher->getRoles());
        self::assertSame($max_groups, $teacher->getMaxGroups());
    }

    public function testCreateTeacherDuplicateEmail(): void
    {
        // Arrange
        $email = 'john.smith@example.com';
        $existing_teacher = new Teacher('John', 'Smith', $email, 'password123');

        $this->teacher_repository->expects(self::once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($existing_teacher);

        // Act & Assert
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Teacher with email {$email} already exists");

        $this->teacher_service->create(
            'John',
            'Smith',
            $email,
            'password123'
        );
    }

    public function testUpdateTeacherSuccess(): void
    {
        // Arrange
        $teacher = new Teacher('John', 'Smith', 'john.smith@example.com', 'password123');
        $this->setEntityId($teacher, 1);

        $new_first_name = 'Jane';
        $new_last_name = 'Doe';
        $new_email = 'jane.doe@example.com';
        $new_max_groups = 3;

        $this->teacher_repository->expects(self::once())
            ->method('findById')
            ->with($teacher->getId())
            ->willReturn($teacher);

        $this->teacher_repository->expects(self::exactly(2))
            ->method('save')
            ->with($teacher);

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === TeacherUpdatedEvent::NAME;
                }),
                TeacherUpdatedEvent::NAME
            );

        // Act
        $this->teacher_service->update(
            $teacher,
            $new_first_name,
            $new_last_name,
            $new_email,
            $new_max_groups
        );

        // Assert
        self::assertSame($new_first_name, $teacher->getFirstName());
        self::assertSame($new_last_name, $teacher->getLastName());
        self::assertSame($new_email, $teacher->getEmail());
        self::assertSame($new_max_groups, $teacher->getMaxGroups());
    }
}
