<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\Teacher;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TeacherFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $teachers_data = [
            [
                'email' => 'teacher1@example.com',
                'password' => 'password123',
                'name' => 'John',
                'surname' => 'Doe',
                'max_groups' => 3,
            ],
            [
                'email' => 'teacher2@example.com',
                'password' => 'password123',
                'name' => 'Jane',
                'surname' => 'Smith',
                'max_groups' => 2,
            ],
            [
                'email' => 'admin@example.com',
                'password' => 'password123',
                'name' => 'Admin',
                'surname' => 'User',
                'max_groups' => 1,
                'roles' => ['ROLE_ADMIN', 'ROLE_TEACHER'],
            ],
        ];

        foreach ($teachers_data as $teacher_data) {
            $name = new PersonName($teacher_data['name'], $teacher_data['surname']);
            $email = new Email($teacher_data['email']);

            $teacher = new Teacher(
                first_name: $name->getFirstName(),
                last_name: $name->getLastName(),
                email: $email->getValue(),
                password: $teacher_data['password'],
                roles: $teacher_data['roles'] ?? ['ROLE_TEACHER'],
                max_groups: $teacher_data['max_groups'],
            );

            $teacher->setPassword(
                $this->password_hasher->hashPassword($teacher, $teacher_data['password'])
            );

            $manager->persist($teacher);
            $this->addReference('teacher_' . $teacher_data['email'], $teacher);
        }

        $manager->flush();
    }
}
