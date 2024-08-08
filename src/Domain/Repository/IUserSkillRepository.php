<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\UserSkill;

interface IUserSkillRepository
{
    public function save(UserSkill $user_skill): void;
    public function removeByUser(User $user): void;

    public function delete(UserSkill $user_skill);
}
