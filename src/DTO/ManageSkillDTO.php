<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Enum\SkillLevel;
use App\Entity\Skill;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ManageSkillDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: [SkillLevel::class, 'stringValues'], strict: true)]
        public string $level,
    ) {
    }

    public static function fromEntity(Skill $skill): self
    {
        return new self(...[
            'name' => $skill->getName(),
            'level' => $skill->getLevel(),
        ]);
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->request->get('name') ?? $request->query->get('name'),
            level: $request->request->get('level') ?? $request->query->get('level') ?? SkillLevel::BASIC,
        );
    }
}