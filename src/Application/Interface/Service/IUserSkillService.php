<?php

declare(strict_types=1);

namespace App\Application\Interface\Service;

use App\Application\Exception\ValidationException;
use App\Domain\Entity\Skill;
use App\Domain\Entity\User;
use App\Domain\Entity\UserSkill;

interface IUserSkillService
{
    /**
     * @throws ValidationException
     */
    public function addUserSkill(User $user, Skill $skill, int $level = 1): void;

    /**
     * Добавление нескольких навыков пользователю
     * @throws ValidationException
     */
    public function addUserSkills(User $user, Skill ...$skills): void;

    public function removeUserSkill(UserSkill $user_skill): void;

    public function removeUserSkills(User $user): void;
}
