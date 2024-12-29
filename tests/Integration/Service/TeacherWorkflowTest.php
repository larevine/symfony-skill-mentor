<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Domain\Entity\Group;
use App\Domain\Entity\Teacher;
use App\Domain\Exception\TeacherException;
use App\Domain\Service\TeacherService;
use App\Domain\Service\SkillService;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class TeacherWorkflowTest extends KernelTestCase
{
    private ?EntityManagerInterface $em;
    private TeacherService $teacher_service;
    private SkillService $skill_service;
    private TagAwareAdapterInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->teacher_service = $container->get(TeacherService::class);
        $this->skill_service = $container->get(SkillService::class);
        $this->cache = $container->get(TagAwareAdapterInterface::class);

        // Clean up test data
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Group g')->execute();
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Teacher t WHERE t.email LIKE :pattern')
            ->setParameter('pattern', '%@example.com')
            ->execute();
        $this->em->createQuery('DELETE FROM App\Domain\Entity\Skill s WHERE s.name LIKE :pattern')
            ->setParameter('pattern', 'Test Skill%')
            ->execute();
    }

    public function testCompleteTeacherWorkflow(): void
    {
        // Create a teacher
        $teacher = $this->teacher_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123'
        );

        // Persist the teacher
        $this->em->persist($teacher);
        $this->em->flush();

        // Get teacher from database
        $teacher = $this->teacher_service->findById(new EntityId($teacher->getId()));

        // Update teacher
        $this->teacher_service->update(
            $teacher,
            'Jane',
            'Smith',
            'jane.smith@example.com',
            15,
        );

        // Assert changes
        self::assertSame('Jane', $teacher->getFirstName());
        self::assertSame('Smith', $teacher->getLastName());
        self::assertSame('jane.smith@example.com', $teacher->getEmail());
    }

    public function testTeacherCacheInvalidation(): void
    {
        // Create a teacher
        $teacher = $this->teacher_service->create(
            'JohnOne',
            'DoeOne',
            'john.doe.one@example.com',
            'password123'
        );

        $teacher_id = $teacher->getId();
        $cache_key = 'teacher_' . $teacher_id;

        // Save to cache
        $cache_item = $this->cache->getItem($cache_key);
        $cache_item->set($teacher);
        $cache_item->tag(['teachers', 'teacher_' . $teacher_id]);
        $this->cache->save($cache_item);

        // Check that data is in cache
        self::assertTrue($this->cache->hasItem($cache_key));

        // Clear cache before update
        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id]);

        // Update teacher
        $this->teacher_service->update(
            $teacher,
            'JaneTwo',
            'SmithTwo',
            'jane.doe.one@example.com',
            15
        );

        // Check that cache is invalidated
        self::assertFalse($this->cache->hasItem($cache_key));
    }

    public function testSkillLevelProgression(): void
    {
        // Create a teacher
        $teacher = $this->teacher_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123'
        );

        // Create a skill
        $skill = $this->skill_service->createSkill('Test Skill 1');

        // Add skill to teacher
        $this->teacher_service->addSkill($teacher, $skill, ProficiencyLevel::fromInt(1));

        // Get teacher from database
        $teacher = $this->teacher_service->findById(new EntityId($teacher->getId()));

        // Assert skill is added
        $teacher_skills = $teacher->getSkills();
        self::assertCount(1, $teacher_skills);
        self::assertSame($skill->getId(), $teacher_skills[0]->getId());
    }

    public function testTeacherGroupAssignmentWorkflow(): void
    {
        // 1. Создание учителя с ограничением в 1 группу
        $teacher = $this->teacher_service->create(
            'John',
            'Doe',
            'john.doe@example.com',
            'password123',
            ['ROLE_TEACHER'],
            1,
        );

        // 2. Создание двух групп
        $group1 = new Group('Group 1');
        $group2 = new Group('Group 2');

        $this->em->persist($group1);
        $this->em->persist($group2);
        $this->em->flush();

        // 3. Назначение в первую группу
        $this->teacher_service->assignToGroup($teacher, $group1);

        $this->em->refresh($teacher);
        self::assertCount(1, $teacher->getTeachingGroups());
        self::assertTrue($teacher->getTeachingGroups()->contains($group1));

        // 4. Попытка назначения во вторую группу должна вызвать исключение
        $this->expectException(TeacherException::class);
        $this->expectExceptionMessage('Teacher has reached maximum number of groups (1)');
        $this->teacher_service->assignToGroup($teacher, $group2);
    }

    public function testTeacherExceedsMaxGroups(): void
    {
        // Создаем учителя с максимум 1 группой
        $teacher = new Teacher(
            'John',
            'Doe',
            'john@example.com',
            'password123',
            ['ROLE_TEACHER'],
            1
        );
        $this->em->persist($teacher);

        // Создаем две группы
        $group1 = new Group('Group 1');
        $group2 = new Group('Group 2');
        $this->em->persist($group1);
        $this->em->persist($group2);
        $this->em->flush();

        // Добавляем первую группу (должно пройти успешно)
        $this->teacher_service->assignToGroup($teacher, $group1);

        // Пытаемся добавить вторую группу (должно вызвать исключение)
        self::expectException(TeacherException::class);
        self::expectExceptionMessage('Teacher has reached maximum number of groups (1)');

        $this->teacher_service->assignToGroup($teacher, $group2);
    }

    public function testConcurrentGroupAssignment(): void
    {
        $teacher = new Teacher(
            'John',
            'Doe',
            'john@example.com',
            'password123',
            ['ROLE_TEACHER'],
            2
        );
        $group1 = new Group('Group 1');
        $group2 = new Group('Group 2');

        $this->em->persist($teacher);
        $this->em->persist($group1);
        $this->em->persist($group2);
        $this->em->flush();

        $this->teacher_service->assignToGroup($teacher, $group1);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Group already has a teacher assigned');

        $this->teacher_service->assignToGroup($teacher, $group1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->clear();
        $this->em->close();
    }
}
