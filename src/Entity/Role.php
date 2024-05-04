<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

#[ORM\Table(name: '`role`')]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Index(name: 'roles__name__idx', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'roles__name__uq', columns: ['name'])]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, unique: true)]
    private int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120, unique: true, nullable: false)]
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

    /**
     * @return array<UserRole>
     */
    public function getUsers(): array
    {
        return $this->users->map(function (UserRole $user) {
            return $user->getUser();
        })->toArray();
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function addUser(UserRole $user_role): void
    {
        if (!$this->users->contains($user_role)) {
            $this->users->add($user_role);
        }
    }

    public function removeUser(UserRole $user_role): void
    {
        if ($this->users->contains($user_role)) {
            $this->users->removeElement($user_role);
        }
    }

    #[ArrayShape([
        'id' => 'int',
        'name' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
