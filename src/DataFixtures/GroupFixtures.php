<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Dev\GroupFixtures as DevGroupFixtures;
use App\DataFixtures\Dev\RoleFixtures;
use App\DataFixtures\Dev\SkillFixtures;
use App\DataFixtures\Dev\UserFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        //
    }

    public function getDependencies(): array
    {
        return [
            SkillFixtures::class,
            RoleFixtures::class,
            UserFixtures::class,
            DevGroupFixtures::class,
        ];
    }
}
