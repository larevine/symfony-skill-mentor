<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Skill;

interface SkillRepositoryInterface
{
    public function findById(int $id): ?Skill;

    /**
     * @return array<Skill>
     */
    public function findAll(): array;

    public function findByName(string $name): ?Skill;

    public function save(Skill $entity): void;

    public function remove(Skill $entity): void;
}
