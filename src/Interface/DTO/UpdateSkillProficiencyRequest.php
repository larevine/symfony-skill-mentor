<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateSkillProficiencyRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $skill_id,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 5)]
        public int $level,
    ) {
    }
}
