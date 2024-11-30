<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\ValueObject\SkillLevel;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TeacherSkillRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $skill_id,
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [SkillLevel::class, 'labels'])]
        public string $level,
    ) {
    }
}
