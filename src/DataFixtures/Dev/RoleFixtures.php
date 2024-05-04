<?php

declare(strict_types=1);

namespace App\DataFixtures\Dev;

use App\Entity\Enum\Default\Roles;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (Roles::cases() as $role) {
            $new_role = new Role();
            $new_role->setName($role->value);
            $manager->persist($new_role);
            $manager->flush();

            $this->addReference($role->name, $new_role);
        }
    }

    public function getOrder(): int
    {
        return 2;
    }
}
