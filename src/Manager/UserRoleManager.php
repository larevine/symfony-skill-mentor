<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserRoleManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function addUserRole(User $user, Role $role): void
    {
        $user_role = new UserRole();
        $user_role->setUser($user);
        $user_role->setRole($role);
        $user->addRole($user_role);
        $role->addUser($user_role);
        $this->em->persist($user_role);
        $this->em->flush();
    }

    public function addUserRoles(User $user, Role ...$roles): void
    {
        foreach ($roles as $role) {
            $this->addUserRole($user, $role);
        }
    }

    public function removeUserRole(User $user, Role $role): void
    {
        $user_role = $this->em->getRepository(UserRole::class)->findOneBy(['user' => $user, 'role' => $role]);
        if ($user_role) {
            $user->removeRole($user_role);
            $role->removeUser($user_role);
            $this->em->remove($user_role);
            $this->em->flush();
        }
    }

    public function removeUserRoles(User $user): void
    {
        $user_roles = $this->em->getRepository(UserRole::class)->findBy(['user' => $user]);
        foreach ($user_roles as $user_role) {
            $user->removeRole($user_role);
            $role = $user_role->getRole();
            $role->removeUser($user_role);
            $this->em->remove($user_role);
        }
        $this->em->flush();
    }
}
