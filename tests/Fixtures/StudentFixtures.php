<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Domain\Entity\Student;
use Doctrine\Persistence\ObjectManager;

class StudentFixtures extends AbstractUserFixture
{
    public const JOHN_STUDENT = [
        'first_name' => 'John',
        'last_name' => 'Student',
        'email' => 'john.student123@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_STUDENT']
    ];

    public const JANE_STUDENT = [
        'first_name' => 'Jane',
        'last_name' => 'Student',
        'email' => 'jane.student@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_STUDENT']
    ];

    public const MIKE_STUDENT = [
        'first_name' => 'Mike',
        'last_name' => 'Student',
        'email' => 'mike.student@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_STUDENT']
    ];

    public const NEW_STUDENT = [
        'first_name' => 'New',
        'last_name' => 'Student',
        'email' => 'new.student@example.com',
        'password' => 'password123',
        'roles' => ['ROLE_STUDENT']
    ];

    public static function getSearchTestSet(): array
    {
        return [
            self::JOHN_STUDENT,
            self::JANE_STUDENT,
            [
                'first_name' => 'Alice',
                'last_name' => 'Student',
                'email' => 'alice.student@example.com',
                'password' => 'password123',
                'roles' => ['ROLE_STUDENT']
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Learner',
                'email' => 'bob.learner@example.com',
                'password' => 'password123',
                'roles' => ['ROLE_STUDENT']
            ],
            [
                'first_name' => 'Charlie',
                'last_name' => 'Student',
                'email' => 'charlie.student@example.com',
                'password' => 'password123',
                'roles' => ['ROLE_STUDENT']
            ]
        ];
    }

    private function createAndPersistStudent(array $data, ObjectManager $manager): void
    {
        $student = new Student(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['password'],
            $data['roles']
        );
        $student->setPassword($this->hashPassword($student, $data['password']));
        $manager->persist($student);
        $this->addReference('student_' . $data['email'], $student);
    }

    public function load(ObjectManager $manager): void
    {
        // Load predefined students
        $this->createAndPersistStudent(self::JOHN_STUDENT, $manager);
        $this->createAndPersistStudent(self::JANE_STUDENT, $manager);
        $this->createAndPersistStudent(self::MIKE_STUDENT, $manager);
        $this->createAndPersistStudent(self::NEW_STUDENT, $manager);

        // Load Search Test Set
        foreach (self::getSearchTestSet() as $data) {
            if ($data === self::JOHN_STUDENT || $data === self::JANE_STUDENT) {
                continue; // Skip already created students
            }
            $this->createAndPersistStudent($data, $manager);
        }

        $manager->flush();
    }
}
