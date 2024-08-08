<?php

declare(strict_types=1);

namespace App\Application\Interface\Service;

use App\Application\Exception\UserNotFoundException;
use App\Application\Exception\ValidationException;
use App\Domain\Entity\Group;

interface IGroupUserService
{
    /**
     * @throws ValidationException
     * @throws UserNotFoundException
     */
    public function addGroupUsers(Group $group, int ...$user_ids): void;

    /**
     * Удаление всех пользователей из группы
     *
     * @param Group $group
     */
    public function removeGroupUsers(Group $group): void;
}
