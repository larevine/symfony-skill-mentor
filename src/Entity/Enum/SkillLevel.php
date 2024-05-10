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

    public static function intValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toString(): string
    {
        return match ($this) {
            self::BASIC => 'BASIC',
            self::INTERMEDIATE => 'INTERMEDIATE',
            self::ADVANCED => 'ADVANCED',
            self::EXPERT => 'EXPERT',
        };
    }

    public static function stringValues(): array
    {
        $result = [];
        foreach (self::cases() as $status) {
            $result[] = $status->toString();
        }
        return $result;
    }

    public static function fromString(string $status): self
    {
        return match ($status) {
            'BASIC' => self::BASIC,
            'INTERMEDIATE' => self::INTERMEDIATE,
            'ADVANCED' => self::ADVANCED,
            'EXPERT' => self::EXPERT,
        };
    }
}
