<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\GroupCapacity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'groups')]
#[ORM\Index(name: 'groups__name__idx', columns: ['name'])]
#[ORM\Index(name: 'groups__teacher_id__idx', columns: ['teacher_id'])]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'groups')]
    #[ORM\JoinColumn(name: 'teacher_id', nullable: false)]
    private Teacher $teacher;

    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'groups')]
    private Collection $students;

    #[ORM\OneToMany(targetEntity: SkillProficiency::class, mappedBy: 'group', cascade: ['persist', 'remove'])]
    private Collection $skills;

    #[ORM\Column(name: 'min_students', type: Types::INTEGER)]
    private int $min_students = 1;

    #[ORM\Column(name: 'max_students', type: Types::INTEGER)]
    private int $max_students = 30;

    private GroupCapacity $capacity;

    public function __construct(
        string $name,
        Teacher $teacher,
        int $min_students = 1,
        int $max_students = 30,
    ) {
        $this->name = $name;
        $this->teacher = $teacher;
        $this->capacity = new GroupCapacity($min_students, $max_students);

        // Сохраняем значения для Doctrine
        $this->min_students = $min_students;
        $this->max_students = $max_students;

        $this->students = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): void
    {
        // Удаляем группу у текущего учителя
        if ($this->teacher !== $teacher) {
            $this->teacher->removeGroup($this);
        }

        $this->teacher = $teacher;
        $teacher->addGroup($this);
    }

    public function addTeacher(Teacher $teacher): void
    {
        $this->setTeacher($teacher);
    }

    public function removeTeacher(Teacher $teacher): void
    {
        if ($this->teacher === $teacher) {
            $this->teacher->removeGroup($this);
            $this->teacher = $teacher; // Сохраняем текущего учителя, так как поле не может быть null
        }
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): void
    {
        if (!$this->capacity->canAcceptMoreStudents($this->students->count())) {
            throw new DomainException('Group has reached maximum capacity');
        }

        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addGroup($this);
        }
    }

    public function removeStudent(Student $student): void
    {
        if ($this->students->removeElement($student)) {
            $student->removeGroup($this);
        }
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getRequiredSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(SkillProficiency $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setGroup($this);
        }
    }

    public function removeSkill(SkillProficiency $skill): void
    {
        if ($this->skills->removeElement($skill)) {
            $skill->setGroup(null);
        }
    }

    public function removeSkillBySkill(Skill $skill): void
    {
        foreach ($this->skills as $skillProficiency) {
            if ($skillProficiency->getSkill() === $skill) {
                $this->removeSkill($skillProficiency);
                break;
            }
        }
    }

    public function getMinStudents(): int
    {
        return $this->min_students;
    }

    public function getMaxStudents(): int
    {
        return $this->max_students;
    }

    public function canAcceptMoreStudents(): bool
    {
        return $this->students->count() < $this->max_students;
    }

    public function hasMinimumStudents(): bool
    {
        return $this->students->count() >= $this->min_students;
    }

    public function getRemainingCapacity(): int
    {
        return $this->max_students - $this->students->count();
    }

    public function getCapacity(): GroupCapacity
    {
        return $this->capacity;
    }

    public function getMinStudentsValue(): int
    {
        return $this->min_students;
    }

    public function getMaxStudentsValue(): int
    {
        return $this->max_students;
    }

    public function setCapacity(int $min_students, int $max_students): void
    {
        $this->capacity = new GroupCapacity($min_students, $max_students);
        $this->min_students = $min_students;
        $this->max_students = $max_students;
    }

    #[ORM\PostLoad]
    public function initializeCapacity(): void
    {
        $this->capacity = new GroupCapacity($this->min_students, $this->max_students);
    }
}
