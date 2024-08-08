<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\SkillLevelEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ManageSkillDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [SkillLevelEnum::class, 'cases'], strict: true)]
        public SkillLevelEnum $level,
    ) {
    }
}
