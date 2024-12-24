<?php

declare(strict_types=1);

namespace App\Interface\DTO;

final class AddSkillRequest
{
    public function __construct(
        public readonly int $skill_id,
        public readonly int $level,
    ) {
    }
}
