<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Entity\Student;
use App\Domain\Event\Student\StudentCreatedEvent;
use App\Domain\Event\Student\StudentUpdatedEvent;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Domain\Service\StudentService;
use App\Infrastructure\Repository\Doctrine\GroupRepository;
use App\Infrastructure\Repository\Doctrine\SkillRepository;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StudentServiceTest extends TestCase
{
    private StudentRepositoryInterface $student_repository;
    private GroupRepositoryInterface $group_repository;
    private SkillRepositoryInterface $skill_repository;
    private ProducerInterface $domain_events_producer;
    private StudentService $student_service;

    protected function setUp(): void
    {
        $this->student_repository = $this->createMock(StudentRepositoryInterface::class);
        $this->group_repository = $this->createMock(GroupRepository::class);
        $this->skill_repository = $this->createMock(SkillRepository::class);
        $this->domain_events_producer = $this->createMock(ProducerInterface::class);

        $this->student_service = new StudentService(
            $this->student_repository,
            $this->skill_repository,
            $this->group_repository,
            $this->domain_events_producer,
        );
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    public function testCreateStudentSuccess(): void
    {
        // Arrange
        $first_name = 'John';
        $last_name = 'Smith';
        $email = 'john.smith@example.com';
        $password = 'password123';

        $this->student_repository->expects(self::once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn(null);

        $this->student_repository->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (Student $student) use ($first_name, $last_name, $email) {
                $this->setEntityId($student, 1);
                return $student;
            });

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === StudentCreatedEvent::NAME;
                }),
                StudentCreatedEvent::NAME
            );

        // Act
        $student = $this->student_service->create(
            $first_name,
            $last_name,
            $email,
            $password
        );

        // Assert
        self::assertSame($first_name, $student->getFirstName());
        self::assertSame($last_name, $student->getLastName());
        self::assertSame($email, $student->getEmail());
    }

    public function testCreateStudentDuplicateEmail(): void
    {
        // Arrange
        $email = 'john.smith@example.com';
        $existing_student = new Student('John', 'Smith', $email, 'password123');

        $this->student_repository->expects(self::once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($existing_student);

        // Act & Assert
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Student with email {$email} already exists");

        $this->student_service->create(
            'John',
            'Smith',
            $email,
            'password123'
        );
    }

    public function testUpdateStudentSuccess(): void
    {
        // Arrange
        $student = new Student('John', 'Smith', 'john.smith@example.com', 'password123');
        $this->setEntityId($student, 1);

        $new_first_name = 'Jane';
        $new_last_name = 'Doe';
        $new_email = 'jane.doe@example.com';

        $this->student_repository->expects(self::once())
            ->method('findById')
            ->with($student->getId())
            ->willReturn($student);

        $this->student_repository->expects(self::once())
            ->method('save')
            ->with($student);

        $this->domain_events_producer->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function ($event_data) {
                    $event = json_decode($event_data, true);
                    return $event['event'] === StudentUpdatedEvent::NAME;
                }),
                StudentUpdatedEvent::NAME
            );

        // Act
        $this->student_service->update(
            $student,
            $new_first_name,
            $new_last_name,
            $new_email
        );

        // Assert
        self::assertSame($new_first_name, $student->getFirstName());
        self::assertSame($new_last_name, $student->getLastName());
        self::assertSame($new_email, $student->getEmail());
    }
}
