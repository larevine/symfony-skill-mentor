<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Infrastructure\DataFixtures\Dev\GroupFixtures as DevGroupFixtures;
use App\Infrastructure\DataFixtures\Dev\SkillFixtures;
use App\Infrastructure\DataFixtures\Dev\UserFixtures;
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
            UserFixtures::class,
            DevGroupFixtures::class,
        ];
    }
}
