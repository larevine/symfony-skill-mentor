<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\DTO\ManageSkillDTO;
use App\Domain\Entity\Skill;
use App\Domain\ValueObject\SkillLevelEnum;
use Symfony\Component\HttpFoundation\Request;

class ManageSkillDTOFactory
{
    public static function createFromEntity(Skill $skill): ManageSkillDTO
    {
        return new ManageSkillDTO(...[
            'name' => $skill->getName(),
            'level' => $skill->getLevel(),
        ]);
    }

    public static function createFromRequest(Request $request): ManageSkillDTO
    {
        return new ManageSkillDTO(
            name: $request->request->getString('name') ?? $request->query->getString('name'),
            level: $request->request->get('level') ?? $request->query->get('level') ?? SkillLevelEnum::BASIC,
        );
    }
}
