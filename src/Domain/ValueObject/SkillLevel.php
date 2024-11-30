<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObjectException;

/**
 * Уровень владения навыком
 */
enum SkillLevel: int
{
    case LEVEL_1 = 1;
    case LEVEL_2 = 2;
    case LEVEL_3 = 3;
    case LEVEL_4 = 4;
    case LEVEL_5 = 5;

    public static function fromInt(int $value): self
    {
        return match ($value) {
            1 => self::LEVEL_1,
            2 => self::LEVEL_2,
            3 => self::LEVEL_3,
            4 => self::LEVEL_4,
            5 => self::LEVEL_5,
            default => throw ValueObjectException::invalidSkillLevel($value),
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::LEVEL_1 => 'Начальный',
            self::LEVEL_2 => 'Ниже среднего',
            self::LEVEL_3 => 'Средний',
            self::LEVEL_4 => 'Выше среднего',
            self::LEVEL_5 => 'Продвинутый',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'Начальный' => self::LEVEL_1,
            'Ниже среднего' => self::LEVEL_2,
            'Средний' => self::LEVEL_3,
            'Выше среднего' => self::LEVEL_4,
            'Продвинутый' => self::LEVEL_5,
        };
    }

    public static function labels(): array
    {
        return array_map(static fn (self $level) => $level->getLabel(), self::cases());
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public static function choices(): array
    {
        return [
            'Начальный' => self::LEVEL_1->value,
            'Ниже среднего' => self::LEVEL_2->value,
            'Средний' => self::LEVEL_3->value,
            'Выше среднего' => self::LEVEL_4->value,
            'Продвинутый' => self::LEVEL_5->value,
        ];
    }
}
