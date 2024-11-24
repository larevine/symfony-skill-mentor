<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\Student;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StudentFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $students_data = [
            [
                'email' => 'student1@example.com',
                'password' => 'password123',
                'first_name' => 'Alex',
                'last_name' => 'Johnson',
            ],
            [
                'email' => 'student2@example.com',
                'password' => 'password123',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
            ],
            [
                'email' => 'student3@example.com',
                'password' => 'password123',
                'first_name' => 'David',
                'last_name' => 'Lee',
            ],
        ];

        foreach ($students_data as $student_data) {
            $name = new PersonName($student_data['first_name'], $student_data['last_name']);
            $email = new Email($student_data['email']);

            $student = new Student(
                first_name: $name->getFirstName(),
                last_name: $name->getLastName(),
                email: $email->getValue(),
            );

            $student->setPassword(
                $this->password_hasher->hashPassword($student, $student_data['password'])
            );

            $manager->persist($student);
            $this->addReference('student_' . $student_data['email'], $student);
        }

        $manager->flush();
    }
}
