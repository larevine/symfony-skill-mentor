<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\UserStatus;
use App\Repository\UserRepository;
use App\Trait\EntityTimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`users`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(name: 'users__email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'users__email', columns: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User
{
    use EntityTimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private readonly int|string $id;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 150, nullable: false)]
    private string $email;

//    #[ORM\Column(name: 'password', type: Types::STRING, length: 150, nullable: false)]
//    private string $password;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'surname', type: Types::STRING, length: 120, nullable: false)]
    private string $surname;

    #[ORM\Column(name: 'status', type: Types::SMALLINT, nullable: false, enumType: UserStatus::class, options: ['default' => UserStatus::ACTIVE])]
    private int $status;

    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user')]
    private Collection $roles;

    #[ORM\OneToMany(targetEntity: GroupUser::class, mappedBy: 'user')]
    private Collection $groups;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    // TODO: hash password
//    public function setPassword(string $password): void
//    {
//        $this->password = $password;
//    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function addRole(UserRole $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function addGroup(GroupUser $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->getFullName()
        ];
    }
}
