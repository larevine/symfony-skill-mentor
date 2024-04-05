<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`roles`')]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Index(name: 'roles__name', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'roles__name', columns: ['name'])]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, unique: true)]
    private readonly int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120)]
    private string $name;

    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'role')]
    private Collection $users;

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
