<?php

declare(strict_types=1);

namespace App\Application\Interface\Service\Management;

use App\Application\DTO\ManageUserDTO;
use Exception;

interface IUserManagementService
{
    /**
     * Создание пользователя со связанными сущностями (навыки)
     *
     * @throws Exception
     */
    public function saveUserWithRelatedEntities(ManageUserDTO $user_DTO): int;

    /**
     * Обновление пользователя со связанными сущностями (навыки)
     *
     * @throws Exception
     */
    public function updateUserWithRelatedEntities(int $user_id, ManageUserDTO $user_DTO): int;

    /**
     * Удаление пользователя со связанными сущностями
     *
     * @throws Exception
     */
    public function deleteUserWithRelatedEntities(int $user_id): bool;
}
