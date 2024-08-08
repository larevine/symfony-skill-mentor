<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Group;
use App\Domain\Entity\GroupUser;
use App\Domain\Entity\User;
use App\Domain\Repository\IGroupUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class GroupUserRepository extends EntityRepository implements IGroupUserRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(GroupUser::class));
    }

    public function findByGroupAndUser(Group $group, User $user): ?GroupUser
    {
        return $this->em->getRepository(GroupUser::class)
            ->findOneBy(['group' => $group, 'user' => $user]);
    }

    public function save(GroupUser $group_user): void
    {
        $this->em->persist($group_user);
        $this->em->flush();
    }

    public function delete(GroupUser $group_user): void
    {
        $this->em->remove($group_user);
        $this->em->flush();
    }

    public function removeByGroup(Group $group): void
    {
        $group_users = $this->em->getRepository(GroupUser::class)
            ->findBy(['group' => $group]);
        foreach ($group_users as $group_user) {
            $this->em->remove($group_user);
        }
        $this->em->flush();
    }
}
