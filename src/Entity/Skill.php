<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\SkillLevel;
use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

#[ORM\Table(name: '`skills`', options: ['comment' => 'Навык и уровень владения'])]
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Index(name: 'skills__name_level__idx', columns: ['name', 'level'])]
#[ORM\UniqueConstraint(name: 'skills__name_level__uq', columns: ['name', 'level'])]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, unique: true)]
    private int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'level', type: Types::SMALLINT, enumType: SkillLevel::class, options: ['default' => 1])]
    private SkillLevel $level;

    #[ORM\OneToMany(targetEntity: UserSkill::class, mappedBy: 'skill')]
    private Collection $users;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->level->toString();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLevel(): SkillLevel
    {
        return $this->level;
    }

    public function setLevel(SkillLevel $level): void
    {
        $this->level = $level;
    }

    /**
     * @return array<UserSkill>
     */
    public function getUsers(): array
    {
        return $this->users->map(function (UserSkill $user) {
            return $user->getUser();
        })->toArray();
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function addUser(UserSkill $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(UserSkill $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    #[ArrayShape([
        'id' => 'int',
        'name' => 'string',
        'level' => 'string'
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level->toString(),
        ];
    }
}
