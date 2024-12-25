<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Domain\Entity\Group;
use App\Domain\Entity\Teacher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public const MATH_GROUP = [
        'name' => 'Math Group',
        'min_students' => 5,
        'max_size' => 15,
        'teacher_reference' => 'teacher_john.teacher@example.com'
    ];

    public const PHYSICS_GROUP = [
        'name' => 'Physics Group',
        'min_students' => 5,
        'max_size' => 10,
        'teacher_reference' => 'teacher_john.teacher@example.com'
    ];

    public const CHEMISTRY_GROUP = [
        'name' => 'Chemistry Group',
        'min_students' => 5,
        'max_size' => 15,
        'teacher_reference' => 'teacher_john.teacher@example.com'
    ];

    public static function getSearchTestSet(): array
    {
        return [
            [
                'name' => 'Math 101',
                'min_students' => 5,
                'max_size' => 15,
                'teacher_reference' => 'teacher_john.teacher@example.com'
            ],
            [
                'name' => 'Advanced Math',
                'min_students' => 5,
                'max_size' => 10,
                'teacher_reference' => 'teacher_john.teacher@example.com'
            ],
            [
                'name' => 'Physics 101',
                'min_students' => 5,
                'max_size' => 15,
                'teacher_reference' => 'teacher_john.teacher@example.com'
            ]
        ];
    }

    private function createAndPersistGroup(array $data, ObjectManager $manager, string $reference): void
    {
        /** @var Teacher $teacher */
        $teacher = $this->getReference($data['teacher_reference'], Teacher::class);

        $group = new Group(
            $data['name'],
            $teacher,
            $data['min_students'],
            $data['max_size']
        );
        $manager->persist($group);
        $this->addReference($reference, $group);
    }

    public function load(ObjectManager $manager): void
    {
        // Load predefined groups
        $this->createAndPersistGroup(self::MATH_GROUP, $manager, 'group-math');
        $this->createAndPersistGroup(self::PHYSICS_GROUP, $manager, 'group-physics');
        $this->createAndPersistGroup(self::CHEMISTRY_GROUP, $manager, 'group-chemistry');

        // Load Search Test Set
        foreach (self::getSearchTestSet() as $key => $data) {
            $this->createAndPersistGroup($data, $manager, 'group-search-' . $key);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeacherFixtures::class,
        ];
    }
}
