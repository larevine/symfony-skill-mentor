<?php

declare(strict_types=1);

namespace App\Interface\DTO;

readonly class AddTeacherSkillRequest
{
    public function __construct(
        public int $skill_id,
        public int $level,
    ) {
    }
}
