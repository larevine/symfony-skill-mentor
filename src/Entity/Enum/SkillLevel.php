<?php

declare(strict_types=1);

namespace App\Entity\Enum;

/**
 * Уровень владения навыком
 */
enum SkillLevel: int
{
    case BASIC = 1;
    case INTERMEDIATE = 2;
    case ADVANCED = 3;
    case EXPERT = 4;
}