<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Group;
use App\Domain\Entity\GroupUser;
use App\Domain\Entity\User;

interface IGroupUserRepository
{
    public function findByGroupAndUser(Group $group, User $user): ?GroupUser;
    public function save(GroupUser $group_user): void;
    public function removeByGroup(Group $group): void;

    public function delete(GroupUser $group_user): void;
}
