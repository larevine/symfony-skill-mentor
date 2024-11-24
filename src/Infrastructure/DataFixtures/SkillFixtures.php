<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SkillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $skills_data = [
            [
                'name' => 'English Grammar',
                'description' => 'Understanding and using English grammar rules correctly',
                'reference' => 'skill_grammar',
            ],
            [
                'name' => 'English Speaking',
                'description' => 'Verbal communication skills in English',
                'reference' => 'skill_speaking',
            ],
            [
                'name' => 'English Writing',
                'description' => 'Written communication skills in English',
                'reference' => 'skill_writing',
            ],
            [
                'name' => 'English Reading',
                'description' => 'Reading comprehension in English',
                'reference' => 'skill_reading',
            ],
            [
                'name' => 'English Listening',
                'description' => 'Understanding spoken English',
                'reference' => 'skill_listening',
            ],
        ];

        foreach ($skills_data as $skill_data) {
            $skill = new Skill(
                name: $skill_data['name'],
                description: $skill_data['description'],
            );

            $manager->persist($skill);
            $this->addReference($skill_data['reference'], $skill);
        }

        $manager->flush();
    }
}
