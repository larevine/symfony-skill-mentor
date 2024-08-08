<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\DTO\ManageUserDTO;
use App\Domain\Entity\Skill;
use App\Domain\Entity\User;
use App\Domain\ValueObject\UserStatusEnum;
use Symfony\Component\HttpFoundation\Request;

class ManageUserDTOFactory
{
    public static function createFromEntity(User $user): ManageUserDTO
    {
        return new ManageUserDTO(...[
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'status' => $user->getStatus(),
            'roles' => $user->getRoles(),
            'skill_ids' => array_map(
                static function (Skill $skill) {
                    return $skill->getId();
                },
                $user->getSkills()
            ),
            'finish_date' => null,
        ]);
    }

    public static function createFromRequest(Request $request): ManageUserDTO
    {
        return new ManageUserDTO(
            email: $request->request->getString('email') ?? $request->query->getString('email'),
            password: $request->request->getString('password') ?? $request->query->getString('password'),
            name: $request->request->getString('name') ?? $request->query->getString('name'),
            surname: $request->request->getString('surname') ?? $request->query->getString('surname'),
            status: $request->request->get('status') ?? $request->query->get('status') ?? UserStatusEnum::ACTIVE,
            roles: $request->request->get('roles') ?? $request->query->get('roles') ?? [],
            skill_ids: $request->request->get('skill_ids') ?? $request->query->get('skill_ids') ?? [],
        );
    }
}
