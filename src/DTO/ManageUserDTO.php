<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Enum\UserStatus;
use App\Entity\Role;
use App\Entity\Skill;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class ManageUserDTO
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank]
        #[Assert\Length(max: 150)]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $surname,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [UserStatus::class, 'stringValues'], strict: true)]
        public string $status,

        #[Assert\Valid]
        #[Type('array<string>')]
        public array $roles,

        #[Assert\Valid]
        #[Type('array<int>')]
        public array $skill_ids,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(...[
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'status' => $user->getStatus(),
            'roles' => array_map(
                static function (Role $role) {
                    return [
                        'id' => $role->getId(),
                        'name' => $role->getName(),
                    ];
                },
                $user->getRoles()
            ),
            'skills' => array_map(
                static function (Skill $skill) {
                    return [
                        'id' => $skill->getId(),
                        'name' => $skill->getName(),
                        'level' => $skill->getLevel(),
                    ];
                },
                $user->getSkills()
            ),
            'finish_date' => null,
        ]);
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->request->get('email') ?? $request->query->get('email'),
            name: $request->request->get('name') ?? $request->query->get('name'),
            surname: $request->request->get('surname') ?? $request->query->get('surname'),
            status: $request->request->get('status') ?? $request->query->get('status') ?? UserStatus::ACTIVE,
            roles: $request->request->get('roles') ?? $request->query->get('roles') ?? [],
            skill_ids: $request->request->get('skill_ids') ?? $request->query->get('skill_ids') ?? [],
        );
    }
}