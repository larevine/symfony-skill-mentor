<?php

declare(strict_types=1);

namespace App\Entity\Enum\Default;

/**
 * Навыки по умолчанию
 */
enum Skills: string
{
    case PHP = 'PHP';
    case SYMFONY = 'Symfony';
    case LARAVEL = 'Laravel';
    case YII = 'Yii';
    case JAVASCRIPT = 'JavaScript';
    case TYPESCRIPT = 'TypeScript';
    case JAVA = 'Java';
    case KOTLIN = 'Kotlin';
    case CSHARP = 'C#';
    case PYTHON = 'Python';
}