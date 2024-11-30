<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\Group;
use App\Domain\Entity\Teacher;
use App\Domain\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $groups_data = [
            [
                'name' => 'English Beginners',
                'min_students' => 5,
                'max_size' => 15,
                'teacher_ref' => 'teacher_teacher1@example.com',
                'students_refs' => [
                    'student_student1@example.com',
                    'student_student2@example.com',
                ],
            ],
            [
                'name' => 'English Intermediate',
                'min_students' => 3,
                'max_size' => 10,
                'teacher_ref' => 'teacher_teacher2@example.com',
                'students_refs' => [
                    'student_student3@example.com',
                ],
            ],
            [
                'name' => 'English Advanced',
                'min_students' => 2,
                'max_size' => 8,
                'teacher_ref' => 'teacher_teacher1@example.com',
                'students_refs' => [],
            ],
        ];

        foreach ($groups_data as $group_data) {
            $teacher = $this->getReference($group_data['teacher_ref'], Teacher::class);
            $group = new Group(
                name: $group_data['name'],
                teacher: $teacher,
                min_students: $group_data['min_students'],
                max_students: $group_data['max_size']
            );

            foreach ($group_data['students_refs'] as $student_ref) {
                $student = $this->getReference($student_ref, Student::class);
                $group->addStudent($student);
            }

            $manager->persist($group);
            $this->addReference('group_' . $group_data['name'], $group);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeacherFixtures::class,
            StudentFixtures::class,
        ];
    }
}
