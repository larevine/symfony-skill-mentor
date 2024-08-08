<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Exception;

class SkillNotFoundException extends Exception
{
    public function __construct(int $skill_id)
    {
        parent::__construct('Skill with ID ' . $skill_id . ' not found.');
    }
}
