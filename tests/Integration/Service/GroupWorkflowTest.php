<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Domain\Exception\TeacherException;
use App\Domain\Service\GroupService;
use App\Domain\Service\TeacherService;
use App\Domain\Service\StudentService;
use App\Domain\ValueObject\EntityId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class GroupWorkflowTest extends KernelTestCase
{
    private GroupService $group_service;
    private TeacherService $teacher_service;
    private StudentService $student_service;
    private EntityManagerInterface $em;
    private TagAwareAdapterInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->group_service = $container->get(GroupService::class);
        $this->teacher_service = $container->get(TeacherService::class);
        $this->student_service = $container->get(StudentService::class);
        $this->em = $container->get('doctrine')->getManager();
        $this->cache = $container->get(TagAwareAdapterInterface::class);

        // Clean up test data
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Group g')->execute();
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Teacher t WHERE t.email LIKE :pattern')
            ->setParameter('pattern', '%@example.com')
            ->execute();
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Student s WHERE s.email LIKE :pattern')
            ->setParameter('pattern', '%@example.com')
            ->execute();
    }

    public function testCreateGroupAndAssignTeacher(): void
    {
        // Create a teacher first
        $teacher = $this->teacher_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123'
        );

        // Create a group
        $group = $this->group_service->create(
            'Test Group A',
            [],
            1,
            10
        );

        // Assign teacher to group
        $this->group_service->assignTeacher($group, $teacher);

        // Get group from database
        $group = $this->group_service->findById(new EntityId($group->getId()));

        // Assert teacher is assigned
        self::assertNotNull($group->getTeacher());
        self::assertSame($teacher->getId(), $group->getTeacher()->getId());
    }

    public function testAddStudentsToGroup(): void
    {
        // Create a group
        $group = $this->group_service->create(
            'Test Group B',
            [],
            1,
            10
        );

        // Create students
        $student1 = $this->student_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123'
        );

        $student2 = $this->student_service->create(
            'Jane',
            'Smith',
            'jane.smith@example.com',
            'password123'
        );

        // Add students to group
        $this->group_service->addStudent($group, $student1);
        $this->group_service->addStudent($group, $student2);

        // Get group from database
        $group = $this->group_service->findById(new EntityId($group->getId()));

        // Assert students are added
        $students = $group->getStudents();
        self::assertCount(2, $students);

        $student_ids = array_map(fn ($student) => $student->getId(), $students->toArray());
        self::assertContains($student1->getId(), $student_ids);
        self::assertContains($student2->getId(), $student_ids);
    }

    public function testGroupTeacherWorkload(): void
    {
        // Create a teacher with max 1 group
        $teacher = $this->teacher_service->create(
            'John',
            'Doe',
            'john.teacher@example.com',
            'password123',
            ['ROLE_TEACHER'],
            1  // Set max_groups to 1
        );

        // Create first group
        $group1 = $this->group_service->create(
            'Test Group 1',
            [],
            1,
            10
        );

        // Create second group
        $group2 = $this->group_service->create(
            'Test Group 2',
            [],
            1,
            10
        );

        // Assign teacher to first group
        $this->group_service->assignTeacher($group1, $teacher);

        // Trying to assign teacher to second group should throw exception
        $this->expectException(TeacherException::class);
        $this->group_service->assignTeacher($group2, $teacher);
    }

    public function testGroupCacheInvalidation(): void
    {
        // Create a group
        $group = $this->group_service->create(
            'Test Group',
            [],
            1,
            10
        );

        $group_id = $group->getId();
        $cache_key = 'group_' . $group_id;

        // Save to cache
        $cache_item = $this->cache->getItem($cache_key);
        $cache_item->set($group);
        $cache_item->tag(['groups', 'group_' . $group_id]);
        $this->cache->save($cache_item);

        // Check that data is in cache
        self::assertTrue($this->cache->hasItem($cache_key));

        // Clear cache before update
        $this->cache->invalidateTags(['groups', 'group_' . $group_id]);

        // Update group name
        $this->group_service->update($group, 'Updated Group Name');

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
