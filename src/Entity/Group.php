<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GroupRepository;
use App\Trait\EntityTimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`groups`')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Index(name: 'group__name', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'group__name', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'group__skill_id', columns: ['skill_id'])]
#[ORM\UniqueConstraint(name: 'group__grade_id', columns: ['grade_id'])]
#[ORM\HasLifecycleCallbacks]
class Group
{
    use EntityTimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private readonly int|string $id;

    #[ORM\Column(type: Types::STRING, length: 120)]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    private int $limit_teachers = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $limit_students = 0;

    #[ORM\OneToMany(targetEntity: GroupUser::class, mappedBy: 'group')]
    private Collection $users;

    #[ORM\OneToOne(targetEntity: Skill::class)]
    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id')]
    private Skill $skill;

    #[ORM\OneToOne(targetEntity: Grade::class)]
    #[ORM\JoinColumn(name: 'grade_id', referencedColumnName: 'id')]
    private Grade $grade;

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

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function addUser(GroupUser $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }
}
