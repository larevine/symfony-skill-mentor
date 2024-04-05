<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\SkillLevel;
use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`skill`')]
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Index(name: 'skill__name_level', columns: ['name', 'level'])]
#[ORM\UniqueConstraint(name: 'skill__name_level', columns: ['name', 'level'])]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, unique: true)]
    private readonly int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120)]
    private string $name;

    #[ORM\Column(name: 'level', type: Types::SMALLINT, enumType: SkillLevel::class, options: ['default' => 1])]
    private int $level;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
