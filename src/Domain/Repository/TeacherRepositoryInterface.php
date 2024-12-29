<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Teacher;
use App\Interface\DTO\Filter\TeacherFilterRequest;

interface TeacherRepositoryInterface
{
    public function findById(int $id): ?Teacher;

    public function findOneByEmail(string $email): ?Teacher;

    /**
     * @return array<Teacher>
     */
    public function findAll(): array;

    /**
     * @return array<Teacher>
     */
    public function findByFilter(TeacherFilterRequest $filter): array;

    public function save(Teacher $entity): void;

    public function remove(Teacher $entity): void;
}
