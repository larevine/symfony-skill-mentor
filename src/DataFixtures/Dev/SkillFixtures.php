<?php

declare(strict_types=1);

namespace App\DataFixtures\Dev;

use App\Entity\Enum\Default\Skills;
use App\Entity\Enum\SkillLevel;
use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SkillFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (Skills::cases() as $skill) {
            foreach (SkillLevel::cases() as $level) {
                $new_skill = new Skill();
                $new_skill->setName($skill->value);
                $new_skill->setLevel($level);
                $manager->persist($new_skill);
                $manager->flush();

                $this->addReference($skill->name.'_'.$level->toString(), $new_skill);
            }
        }
    }

    public function getOrder(): int
    {
        return 1;
    }
}
