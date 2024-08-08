<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\SkillLevelEnum;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class ManageGroupDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',
        #[Assert\PositiveOrZero]
        public int $limit_teachers = 0,
        #[Assert\PositiveOrZero]
        public int $limit_students = 0,
        #[Assert\NotBlank]
        public ?int $skill_id = null,
        #[Assert\Choice(callback: [SkillLevelEnum::class, 'cases'], strict: true)]
        public SkillLevelEnum $level = SkillLevelEnum::BASIC,
        #[Assert\Valid]
        #[Type('array<int>')]
        public array $user_ids = [],
    ) {
    }
}
