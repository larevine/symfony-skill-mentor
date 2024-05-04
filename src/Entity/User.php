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
use JetBrains\PhpStorm\ArrayShape;

#[ORM\Table(name: '`users`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(name: 'users__email__idx', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'users__email__uq', columns: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User
{
    use EntityTimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private int|string $id;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 150, nullable: false)]
    private string $email;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'surname', type: Types::STRING, length: 120, nullable: false)]
    private string $surname;

    #[ORM\Column(name: 'status', type: Types::SMALLINT, nullable: false, enumType: UserStatus::class, options: ['default' => UserStatus::ACTIVE])]
    private UserStatus $status;

    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user')]
    private Collection $roles;

    #[ORM\OneToMany(targetEntity: GroupUser::class, mappedBy: 'user')]
    private Collection $groups;

    #[ORM\OneToMany(targetEntity: UserSkill::class, mappedBy: 'user')]
    private Collection $skills;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->skills = new ArrayCollection();
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

    public function setStatus(UserStatus $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status->toString();
    }

    public function addRole(UserRole $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(UserRole $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    public function addGroup(GroupUser $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
    }

    public function removeGroup(GroupUser $group): void
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }
    }

    public function addSkill(UserSkill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }
    }

    public function removeSkill(UserSkill $skill): void
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }
    }

    /**
     * @return array<UserRole>
     */
    public function getRoles(): array
    {
        return $this->roles->map(function (UserRole $role) {
            return $role->getRole();
        })->toArray();
    }

    /**
     * @return array<GroupUser>
     */
    public function getGroups(): array
    {
        return $this->groups->map(function (GroupUser $group) {
            return $group->getGroup();
        })->toArray();
    }

    /**
     * @return array<UserSkill>
     */
    public function getSkills(): array
    {
        return $this->skills->map(function (UserSkill $skill) {
            return $skill->getSkill();
        })->toArray();
    }

    #[ArrayShape([
            'id' => 'int',
            'email' => 'string',
            'fullname' => 'string',
            'created_at' => 'string',
            'updated_at' => 'string',
            'roles' => 'string[]',
            'groups' => 'string[]',
            'skills' => 'string[]',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'fullname' => $this->getFullName(),
            'status' => $this->getStatus(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'roles' => array_map(
                static fn(UserRole $role) => [
                    $role->getRole()->getName(),
                ],
                $this->roles->toArray()
            ),
            'groups' => array_map(
                static fn(GroupUser $group) => [
                    $group->getGroup()->getName(),
                ],
                $this->groups->toArray()
            ),
            'skills' => array_map(
                static fn(UserSkill $skill) => [
                    $skill->getGroup()->getName(),
                ],
                $this->skills->toArray()
            ),
        ];
    }
}
