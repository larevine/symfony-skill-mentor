<?php

declare(strict_types=1);

namespace App\Application\Interface\Service\Management;

use App\Application\DTO\ManageGroupDTO;
use App\Application\Exception\GroupNotFoundException;
use App\Application\Exception\SkillNotFoundException;
use App\Application\Exception\ValidationException;
use Exception;

interface IGroupManagementService
{
    /**
     * Создание группы со связанными сущностями (навыки и пользователи)
     *
     * @throws ValidationException
     * @throws SkillNotFoundException
     * @throws Exception
     */
    public function saveGroupWithRelatedEntities(ManageGroupDTO $group_dto): int;

    /**
     * Обновление группы со связанными сущностями (навыки и пользователи)
     *
     * @throws GroupNotFoundException
     * @throws SkillNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateGroupWithRelatedEntities(int $group_id, ManageGroupDTO $group_dto): bool;

    /**
     * Удаление группы со связанными сущностями (пользователями)
     *
     * @throws GroupNotFoundException
     */
    public function deleteGroupWithRelatedEntities(int $group_id): bool;
}
