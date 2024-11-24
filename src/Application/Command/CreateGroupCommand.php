<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\GroupCapacity;
use App\Domain\ValueObject\GroupName;

readonly class CreateGroupCommand
{
    private GroupName $name;
    private GroupCapacity $capacity;

    /**
     * @param int[] $student_ids
     */
    public function __construct(
        string $name,
        private array $student_ids,
        int $min_students,
        int $max_students,
    ) {
        $this->name = new GroupName($name);
        $this->capacity = new GroupCapacity($min_students, $max_students);
    }

    public function getName(): string
    {
        return $this->name->getValue();
    }

    /**
     * @return int[]
     */
    public function getStudentIds(): array
    {
        return $this->student_ids;
    }

    public function getMinStudents(): int
    {
        return $this->capacity->getMinStudents();
    }

    public function getMaxStudents(): int
    {
        return $this->capacity->getMaxStudents();
    }

    public function getGroupName(): GroupName
    {
        return $this->name;
    }

    public function getCapacity(): GroupCapacity
    {
        return $this->capacity;
    }
}
