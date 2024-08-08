<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Skill;

interface ISkillRepository
{
    public function findByName(string $name): ?Skill;

    /**
     * @return Skill[]
     */
    public function findPaginated(int $page, int $per_page): array;
    public function save(Skill $skill): void;
    public function delete(Skill $skill): void;
}
