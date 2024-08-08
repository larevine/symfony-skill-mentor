<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\Trait\TimestampTrait;
use App\Domain\ValueObject\SkillLevelEnum;
use App\Infrastructure\Repository\Doctrine\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

#[ORM\Table(name: '`groups`')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Index(name: 'groups__name__idx', columns: ['name'])]
#[ORM\Index(name: 'groups__skill__idx', columns: ['skill_id'])]
#[ORM\HasLifecycleCallbacks]
class Group
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::BIGINT, unique: true)]
    private int|string $id;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: false)]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    private int $limit_teachers = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $limit_students = 0;

    #[ORM\OneToMany(targetEntity: GroupUser::class, mappedBy: 'group')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: Skill::class)]
    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id', nullable: false)]
    private Skill $skill;

    #[ORM\Column(name: 'level', type: Types::SMALLINT, enumType: SkillLevelEnum::class, options: ['default' => 1])]
    private SkillLevelEnum $level;

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

    public function getLimitTeachers(): int
    {
        return $this->limit_teachers;
    }

    public function setLimitTeachers(int $limit_teachers): void
    {
        $this->limit_teachers = $limit_teachers;
    }

    public function getLimitStudents(): int
    {
        return $this->limit_students;
    }

    public function setLimitStudents(int $limit_students): void
    {
        $this->limit_students = $limit_students;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function setSkill(Skill $skill): void
    {
        $this->skill = $skill;
    }

    public function getLevel(): SkillLevelEnum
    {
        return $this->level;
    }

    public function setLevel(SkillLevelEnum $level): void
    {
        $this->level = $level;
    }

    /**
     * @return array<GroupUser>
     */
    public function getUsers(): array
    {
        return $this->users->map(function (GroupUser $user) {
            return $user->getUser();
        })->toArray();
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

    public function removeUser(GroupUser $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    #[ArrayShape([
        'id' => 'int',
        'name' => 'string',
        'limit_teachers' => 'int',
        'limit_students' => 'int',
        'skill' => 'string',
        'level' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'limit_teachers' => $this->limit_teachers,
            'limit_students' => $this->limit_students,
            'skill' => $this->skill->getName(),
            'level' => $this->level,
        ];
    }
}
