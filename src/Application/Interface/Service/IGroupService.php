<?php

declare(strict_types=1);

namespace App\Application\Interface\Service;

use App\Application\DTO\ManageGroupDTO;
use App\Application\Exception\ValidationException;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;

interface IGroupService
{
    public function findGroup(int $id): ?Group;
    public function saveGroup(Group $group): void;
    public function deleteGroup(Group $group): void;

    /**
     * @return Group[]
     */
    public function findPaginated(int $page, int $per_page): array;

    /**
     * @throws ValidationException
     */
    public function createGroupFromDTO(Group $group, ManageGroupDTO $group_dto, Skill $skill): Group;

    /**
     * @throws ValidationException
     */
    public function updateGroupFromDTO(Group $group, ManageGroupDTO $group_dto, Skill $skill): Group;
}
