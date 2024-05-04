<?php

declare(strict_types=1);

namespace App\Entity\Enum\Default;

/**
 * Роли пользователя по умолчанию
 */
enum Roles: string
{
    case ADMIN = 'ROLE_ADMIN';
    case TEACHER = 'ROLE_TEACHER';
    case STUDENT = 'ROLE_STUDENT';
}