<?php

declare(strict_types=1);

namespace App\Application\Interface\Service;

use App\Application\Exception\ValidationException;
use App\Domain\Entity\Skill;

interface ISkillService
{
    public function findSkillById(int $id): ?Skill;
    public function findSkillByName(string $name): ?Skill;

    /**
     * @throws ValidationException
     */
    public function createSkill(string $name, int $level = 1): Skill;

    /**
     * @throws ValidationException
     */
    public function updateSkill(Skill $skill, string $name, int $level): void;
    public function saveSkill(Skill $skill): void;
    public function deleteSkill(Skill $skill): void;

    /**
     * @return Skill[]
     */
    public function findPaginated(int $page, int $per_page): array;
}
