<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Enum\SkillLevel;
use App\Entity\Group;
use App\Entity\User;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ManageGroupDTO
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,

        #[Assert\PositiveOrZero]
        public int $limit_teachers,

        #[Assert\PositiveOrZero]
        public int $limit_students,

        #[Assert\Positive]
        public int $skill_id,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: [SkillLevel::class, 'stringValues'])]
        public string $level,

        #[Assert\Valid]
        #[Type('array<int>')]
        public array $user_ids,
    ) {
    }

    public static function fromEntity(Group $group): self
    {
        return new self(...[
            'name' => $group->getName(),
            'limit_teachers' => $group->getLimitTeachers(),
            'limit_students' => $group->getLimitStudents(),
            'skill_id' => $group->getSkill()->getId(),
            'level' => $group->getLevel(),
            'user_ids' => array_map(
                static fn (User $user) => $user->getId(),
                $group->getUsers()
            ),
        ]);
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->request->get('name') ?? $request->query->get('name'),
            limit_teachers: $request->request->get('limit_teachers') ?? $request->query->get('limit_teachers'),
            limit_students: $request->request->get('limit_students') ?? $request->query->get('limit_students'),
            skill_id: $request->request->get('skill_id') ?? $request->query->get('skill_id'),
            level: $request->request->get('level') ?? $request->query->get('level'),
            user_ids: $request->request->get('user_ids') ?? $request->query->get('user_ids') ?? [],
        );
    }
}