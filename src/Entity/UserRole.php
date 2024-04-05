<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`user_role`')]
#[ORM\Entity]
#[ORM\Index(name: 'user_role__user_id', columns: ['user_id'])]
#[ORM\Index(name: 'user_role__role_id', columns: ['role_id'])]
#[ORM\UniqueConstraint(name: 'user_role__user_id_role_id', columns: ['user_id', 'role_id'])]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private readonly int|string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'roles')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private Role $role;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }
}
