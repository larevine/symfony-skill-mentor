<?php

declare(strict_types=1);

namespace App\Entity\Enum;

/**
 * Статус пользователя
 */
enum UserStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
}