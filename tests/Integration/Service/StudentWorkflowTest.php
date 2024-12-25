<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Domain\Entity\Student;
use App\Domain\Service\StudentService;
use App\Interface\DTO\Filter\StudentFilterRequest;
use App\Tests\Fixtures\StudentFixtures;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class StudentWorkflowTest extends KernelTestCase
{
    private ?EntityManagerInterface $em;
    private ?StudentService $student_service;
    private TagAwareAdapterInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->student_service = $container->get(StudentService::class);
        $this->em = $container->get('doctrine')->getManager();
        $this->cache = $container->get(TagAwareAdapterInterface::class);

        // Clean up test data
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Student s WHERE s.email LIKE :pattern')
            ->setParameter('pattern', '%@example.com')
            ->execute();
    }

    public function testCreateAndUpdateStudent(): void
    {
        // Create a new student
        $student_data = StudentFixtures::JOHN_STUDENT;
        $student = $this->student_service->create(
            $student_data['first_name'],
            $student_data['last_name'],
            $student_data['email'],
            $student_data['password'],
        );

        self::assertInstanceOf(Student::class, $student);
        self::assertEquals($student_data['first_name'], $student->getFirstName());
        self::assertEquals($student_data['last_name'], $student->getLastName());
        self::assertEquals($student_data['email'], $student->getEmail());

        // Try to find the student
        $filter = new StudentFilterRequest();
        $filter->setSearch($student_data['first_name']);
        $students = $this->student_service->findByFilter($filter);

        self::assertCount(1, $students);
        self::assertEquals($student->getId(), $students[0]->getId());
    }

    public function testPreventDuplicateEmails(): void
    {
        // Create first student
        $student_data = StudentFixtures::JOHN_STUDENT;
        $this->student_service->create(
            $student_data['first_name'],
            $student_data['last_name'],
            $student_data['email'],
            $student_data['password'],
        );

        // Try to create another student with the same email
        self::expectException(DomainException::class);
        self::expectExceptionMessage("Student with email {$student_data['email']} already exists");

        $this->student_service->create(
            'Another',
            'Student',
            $student_data['email'],
            'password123'
        );
    }

    public function testStudentSearch(): void
    {
        // Create multiple students
        $test_data = StudentFixtures::getSearchTestSet();
        foreach ($test_data as $data) {
            $this->student_service->create(
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['password'],
            );
        }

        // Search by name
        $filter = new StudentFilterRequest();
        $filter->setSearch('John');
        $results = $this->student_service->findByFilter($filter);

        self::assertGreaterThan(0, count($results));
        foreach ($results as $student) {
            self::assertStringContainsString('John', $student->getFirstName());
        }

        // Search with pagination
        $filter = new StudentFilterRequest();
        $filter->setPerPage(1);
        $filter->setPage(1);
        $results = $this->student_service->findByFilter($filter);

        self::assertCount(1, $results);
    }

    public function testStudentCacheInvalidation(): void
    {
        // Create a student
        $student = $this->student_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123'
        );

        $student_id = $student->getId();
        $cache_key = 'student_' . $student_id;

        // Save to cache
        $cache_item = $this->cache->getItem($cache_key);
        $cache_item->set($student);
        $cache_item->tag(['students', 'student_' . $student_id]);
        $this->cache->save($cache_item);

        // Check that data is in cache
        self::assertTrue($this->cache->hasItem($cache_key));

        // Clear cache before update
        $this->cache->invalidateTags(['students', 'student_' . $student_id]);

        // Update student
        $this->student_service->update(
            $student,
            'Jane',
            'Smith',
            'jane.doe@example.com'
        );

        // Check that cache is invalidated
        self::assertFalse($this->cache->hasItem($cache_key));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->clear();
        $this->em->close();
    }
}
