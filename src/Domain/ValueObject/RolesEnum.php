<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Роли пользователя по умолчанию
 */
enum RolesEnum: string
{
    case BASE = 'ROLE_BASE'; // Пользователь без ролей
    case ADMIN = 'ROLE_ADMIN'; // Администратор
    case TEACHER = 'ROLE_TEACHER'; // Учитель
    case STUDENT = 'ROLE_STUDENT'; // Студент
}
