<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Domain\Entity\Teacher;
use Doctrine\Persistence\ObjectManager;

class TeacherFixtures extends AbstractUserFixture
{
    public const JOHN_TEACHER = [
        'first_name' => 'John',
        'last_name' => 'Teacher',
        'email' => 'john.teacher@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_TEACHER'],
        'max_groups' => 5
    ];

    public const JANE_TEACHER = [
        'first_name' => 'Jane',
        'last_name' => 'Professor',
        'email' => 'jane.professor@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_TEACHER'],
        'max_groups' => 3
    ];

    public const MAX_LOAD_TEACHER = [
        'first_name' => 'Max',
        'last_name' => 'Load',
        'email' => 'max.load@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_TEACHER'],
        'max_groups' => 2
    ];

    public static function getSearchTestSet(): array
    {
        return [
            self::JOHN_TEACHER,
            self::JANE_TEACHER,
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'password' => 'password123',
                'roles' => ['ROLE_TEACHER'],
                'max_groups' => 4
            ]
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([self::JOHN_TEACHER, self::JANE_TEACHER, self::MAX_LOAD_TEACHER] as $teacher_data) {
            $teacher = new Teacher(
                first_name: $teacher_data['first_name'],
                last_name: $teacher_data['last_name'],
                email: $teacher_data['email'],
                password: $teacher_data['password'],
                roles: $teacher_data['roles'],
                max_groups: $teacher_data['max_groups']
            );

            $teacher->setPassword($this->hashPassword($teacher, $teacher_data['password']));
            $manager->persist($teacher);
            $this->addReference('teacher_' . $teacher_data['email'], $teacher);
        }

        $manager->flush();
    }
}
