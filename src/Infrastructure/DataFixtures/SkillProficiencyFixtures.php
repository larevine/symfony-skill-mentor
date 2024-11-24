<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\SkillProficiency;
use App\Domain\ValueObject\ProficiencyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SkillProficiencyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Teacher proficiencies
        $teacher_proficiencies = [
            [
                'teacher_ref' => 'teacher_teacher1@example.com',
                'skill_ref' => 'skill_grammar',
                'level' => 'expert',
            ],
            [
                'teacher_ref' => 'teacher_teacher1@example.com',
                'skill_ref' => 'skill_speaking',
                'level' => 'expert',
            ],
            [
                'teacher_ref' => 'teacher_teacher2@example.com',
                'skill_ref' => 'skill_writing',
                'level' => 'expert',
            ],
            [
                'teacher_ref' => 'teacher_teacher2@example.com',
                'skill_ref' => 'skill_reading',
                'level' => 'advanced',
            ],
        ];

        foreach ($teacher_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref']),
                level: new ProficiencyLevel($proficiency['level']),
                teacher: $this->getReference($proficiency['teacher_ref']),
            );
            $manager->persist($skill_proficiency);
        }

        // Student proficiencies
        $student_proficiencies = [
            [
                'student_ref' => 'student_student1@example.com',
                'skill_ref' => 'skill_grammar',
                'level' => 'beginner',
            ],
            [
                'student_ref' => 'student_student2@example.com',
                'skill_ref' => 'skill_speaking',
                'level' => 'intermediate',
            ],
            [
                'student_ref' => 'student_student3@example.com',
                'skill_ref' => 'skill_writing',
                'level' => 'advanced',
            ],
        ];

        foreach ($student_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref']),
                level: new ProficiencyLevel($proficiency['level']),
                student: $this->getReference($proficiency['student_ref']),
            );
            $manager->persist($skill_proficiency);
        }

        // Group required proficiencies
        $group_proficiencies = [
            [
                'group_ref' => 'group_English Beginners',
                'skill_ref' => 'skill_grammar',
                'level' => 'beginner',
            ],
            [
                'group_ref' => 'group_English Intermediate',
                'skill_ref' => 'skill_speaking',
                'level' => 'intermediate',
            ],
            [
                'group_ref' => 'group_English Advanced',
                'skill_ref' => 'skill_writing',
                'level' => 'advanced',
            ],
        ];

        foreach ($group_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref']),
                level: new ProficiencyLevel($proficiency['level']),
                group: $this->getReference($proficiency['group_ref']),
            );
            $manager->persist($skill_proficiency);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeacherFixtures::class,
            StudentFixtures::class,
            GroupFixtures::class,
            SkillFixtures::class,
        ];
    }
}
