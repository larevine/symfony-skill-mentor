<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Skill;
use App\Domain\ValueObject\EntityId;

interface SkillServiceInterface
{
    public function findById(EntityId $id): ?Skill;

    /** @return array<Skill> */
    public function findAll(): array;

    public function findByName(string $name): ?Skill;

    public function createSkill(string $name, ?string $description = null): Skill;
}
