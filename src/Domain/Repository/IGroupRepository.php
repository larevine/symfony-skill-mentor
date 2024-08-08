<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Group;

interface IGroupRepository
{
    public function findByName(string $name): ?Group;

    /**
     * @return Group[]
     */
    public function findPaginated(int $page, int $per_page): array;
    public function save(Group $group): void;
    public function delete(Group $group): void;
}
