<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students')]
// API-platform
#[ApiResource]
class Student extends User
{
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'students', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'student_groups')]
    private Collection $groups;

    /**
     * @var Collection<int, SkillProficiency>
     */
    #[ORM\OneToMany(targetEntity: SkillProficiency::class, mappedBy: 'student', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $skill_proficiencies;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        array $roles = ['ROLE_STUDENT']
    ) {
        parent::__construct($first_name, $last_name, $email, $password, $roles);
        $this->groups = new ArrayCollection();
        $this->skill_proficiencies = new ArrayCollection();
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addStudent($this);
        }
    }

    public function removeGroup(Group $group): void
    {
        if ($this->groups->removeElement($group)) {
            $group->removeStudent($this);
        }
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getSkills(): Collection
    {
        return $this->skill_proficiencies;
    }

    public function addSkill(SkillProficiency $skill): void
    {
        if (!$this->skill_proficiencies->contains($skill)) {
            $this->skill_proficiencies->add($skill);
            $skill->setStudent($this);
        }
    }

    public function removeSkill(SkillProficiency $skill): void
    {
        if ($this->skill_proficiencies->removeElement($skill)) {
            $skill->setStudent(null);
        }
    }
}
