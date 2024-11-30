<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\SkillProficiency;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Teacher;
use App\Domain\Entity\Student;
use App\Domain\Entity\Group;
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
                'level' => 'level_5', // expert -> level_5 (Продвинутый)
            ],
            [
                'teacher_ref' => 'teacher_teacher1@example.com',
                'skill_ref' => 'skill_speaking',
                'level' => 'level_5', // expert -> level_5 (Продвинутый)
            ],
            [
                'teacher_ref' => 'teacher_teacher2@example.com',
                'skill_ref' => 'skill_writing',
                'level' => 'level_5', // expert -> level_5 (Продвинутый)
            ],
            [
                'teacher_ref' => 'teacher_teacher2@example.com',
                'skill_ref' => 'skill_reading',
                'level' => 'level_4', // advanced -> level_4 (Выше среднего)
            ],
        ];

        foreach ($teacher_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref'], Skill::class),
                level: new ProficiencyLevel($proficiency['level']),
                teacher: $this->getReference($proficiency['teacher_ref'], Teacher::class),
            );
            $manager->persist($skill_proficiency);
        }

        // Student proficiencies
        $student_proficiencies = [
            [
                'student_ref' => 'student_student1@example.com',
                'skill_ref' => 'skill_grammar',
                'level' => 'level_1', // beginner -> level_1 (Начальный)
            ],
            [
                'student_ref' => 'student_student2@example.com',
                'skill_ref' => 'skill_speaking',
                'level' => 'level_3', // intermediate -> level_3 (Средний)
            ],
            [
                'student_ref' => 'student_student3@example.com',
                'skill_ref' => 'skill_writing',
                'level' => 'level_4', // advanced -> level_4 (Выше среднего)
            ],
        ];

        foreach ($student_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref'], Skill::class),
                level: new ProficiencyLevel($proficiency['level']),
                student: $this->getReference($proficiency['student_ref'], Student::class),
            );
            $manager->persist($skill_proficiency);
        }

        // Group required proficiencies
        $group_proficiencies = [
            [
                'group_ref' => 'group_English Beginners',
                'skill_ref' => 'skill_grammar',
                'level' => 'level_1', // beginner -> level_1 (Начальный)
            ],
            [
                'group_ref' => 'group_English Intermediate',
                'skill_ref' => 'skill_speaking',
                'level' => 'level_3', // intermediate -> level_3 (Средний)
            ],
            [
                'group_ref' => 'group_English Advanced',
                'skill_ref' => 'skill_writing',
                'level' => 'level_4', // advanced -> level_4 (Выше среднего)
            ],
        ];

        foreach ($group_proficiencies as $proficiency) {
            $skill_proficiency = new SkillProficiency(
                skill: $this->getReference($proficiency['skill_ref'], Skill::class),
                level: new ProficiencyLevel($proficiency['level']),
                group: $this->getReference($proficiency['group_ref'], Group::class),
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
