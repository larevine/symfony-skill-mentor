<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\DTO\ManageGroupDTO;
use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\ValueObject\SkillLevelEnum;
use Symfony\Component\HttpFoundation\Request;

class ManageGroupDTOFactory
{
    public function createFromEntity(Group $group): ManageGroupDTO
    {
        return new ManageGroupDTO(
            name: $group->getName(),
            limit_teachers: $group->getLimitTeachers(),
            limit_students: $group->getLimitStudents(),
            skill_id: $group->getSkill()?->getId(),
            level: $group->getLevel(),
            user_ids: array_map(
                static fn (User $user) => $user->getId(),
                $group->getUsers()
            )
        );
    }

    public static function createFromRequest(Request $request): ManageGroupDTO
    {
        return new ManageGroupDTO(
            name: $request->request->getString('name') ?? $request->query->getString('name'),
            limit_teachers: $request->request->getInt('limit_teachers') ?? $request->query->getInt('limit_teachers'),
            limit_students: $request->request->getInt('limit_students') ?? $request->query->getInt('limit_students'),
            skill_id: $request->request->getInt('skill_id') ?? $request->query->getInt('skill_id'),
            level: $request->request->get('level') ?? $request->query->get('level') ?? SkillLevelEnum::BASIC,
            user_ids: $request->request->get('user_ids') ?? $request->query->get('user_ids') ?? [],
        );
    }
}
