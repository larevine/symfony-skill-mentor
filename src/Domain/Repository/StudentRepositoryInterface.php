<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Interface\DTO\Filter\StudentFilterRequest;

interface StudentRepositoryInterface
{
    public function findById(int $id): ?Student;

    public function findOneByEmail(string $email): ?Student;

    /**
     * @return array<Student>
     */
    public function findByFilter(StudentFilterRequest $filter): array;

    /**
     * @return array<Student>
     */
    public function findAll(): array;

    public function save(Student $student): void;

    public function remove(Student $student): void;

    /**
     * Find students without a group assignment
     * @return array<Student>
     */
    public function findWithoutGroup(): array;

    /**
     * Find students eligible for a specific group based on skill requirements
     * @return array<Student>
     */
    public function findEligibleForGroup(Group $group): array;
}
