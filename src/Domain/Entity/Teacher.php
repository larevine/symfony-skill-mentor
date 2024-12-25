<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'teachers')]
// API-platform
#[ApiResource]
class Teacher extends User
{
    #[ORM\OneToMany(targetEntity: Group::class, mappedBy: 'teacher')]
    private Collection $teaching_groups;

    #[ORM\OneToMany(targetEntity: SkillProficiency::class, mappedBy: 'teacher', cascade: ['persist', 'remove'])]
    private Collection $skills;

    #[ORM\Column(name: 'max_groups', type: 'integer')]
    private int $max_groups;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        array $roles = ['ROLE_TEACHER'],
        int $max_groups = 2
    ) {
        parent::__construct($first_name, $last_name, $email, $password, $roles);
        $this->max_groups = $max_groups;
        $this->teaching_groups = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    /**
     * @return Collection<int, Group>
     */
    public function getTeachingGroups(): Collection
    {
        return $this->teaching_groups;
    }

    public function addGroup(Group $group): void
    {
        if (!$this->teaching_groups->contains($group)) {
            $this->teaching_groups->add($group);
            if ($group->getTeacher() !== $this) {
                $group->setTeacher($this);
            }
        }
    }

    public function removeGroup(?Group $group): void
    {
        if ($group !== null && $this->teaching_groups->removeElement($group)) {
            if ($group->getTeacher() === $this) {
                $group->setTeacher(null);
            }
        }
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(SkillProficiency $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setTeacher($this);
        }
    }

    public function removeSkill(SkillProficiency $skill): void
    {
        if ($this->skills->removeElement($skill)) {
            if ($skill->getTeacher() === $this) {
                $skill->setTeacher(null);
            }
        }
    }

    public function getMaxGroups(): int
    {
        return $this->max_groups;
    }

    public function setMaxGroups(int $max_groups): void
    {
        $this->max_groups = $max_groups;
    }
}
