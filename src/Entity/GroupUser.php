<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`group_user`')]
#[ORM\Entity]
#[ORM\Index(name: 'group_user__group_id', columns: ['group_id'])]
#[ORM\Index(name: 'group_user__user_id', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'group_user__group_id_user_id', columns: ['group_id', 'user_id'])]
class GroupUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private readonly int|string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'groups')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    private Group $group;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }
}
