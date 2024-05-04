<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Group;
use App\Entity\GroupUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class GroupUserManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function addGroupUser(Group $group, User $user): void
    {
        $group_user = new GroupUser();
        $group_user->setGroup($group);
        $group_user->setUser($user);
        $user->addGroup($group_user);
        $group->addUser($group_user);
        $this->em->persist($group_user);
        $this->em->flush();
    }

    public function addGroupUsers(Group $group, int ...$user_ids): void
    {
        foreach ($user_ids as $user_id) {
            $user = $this->em->getRepository(User::class)->find($user_id);
            $this->addGroupUser($group, $user);
        }
    }

    public function removeGroupUser(Group $group, User $user): void
    {
        $group_user = $this->em->getRepository(GroupUser::class)->findOneBy(['group' => $group, 'user' => $user]);
        if ($group_user) {
            $user->removeGroup($group_user);
            $group->removeUser($group_user);
            $this->em->remove($group_user);
            $this->em->flush();
        }
    }

    public function removeGroupUsers(Group $group): void
    {
        $group_users = $this->em->getRepository(GroupUser::class)->findBy(['group' => $group]);
        foreach ($group_users as $group_user) {
            $group->removeUser($group_user);
            $user = $group_user->getUser();
            $user->removeGroup($group_user);
            $this->em->remove($group_user);
        }
        $this->em->flush();
    }
}
