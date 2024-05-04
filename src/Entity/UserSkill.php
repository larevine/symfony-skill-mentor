<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`user_skill`')]
#[ORM\Entity]
#[ORM\Index(name: 'user_skill__user_id__idx', columns: ['user_id'])]
#[ORM\Index(name: 'user_skill__skill_id__idx', columns: ['skill_id'])]
#[ORM\UniqueConstraint(name: 'user_skill__skill_id_user_id__uq', columns: ['user_id', 'skill_id'])]
class UserSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private int|string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Skill::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id')]
    private Skill $skill;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function setSkill(Skill $skill): void
    {
        $this->skill = $skill;
    }
}
