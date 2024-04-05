<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`grades`')]
#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Index(name: 'grades__name', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'grades__name', columns: ['name'])]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, unique: true)]
    private readonly int $id;

    #[ORM\Column(type: Types::STRING, length: 120)]
    private string $name;

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
}
