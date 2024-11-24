<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DomainException;

class ProficiencyLevel
{
    private const BEGINNER = 'beginner';
    private const INTERMEDIATE = 'intermediate';
    private const ADVANCED = 'advanced';
    private const EXPERT = 'expert';

    private const LEVEL_VALUES = [
        self::BEGINNER => 1,
        self::INTERMEDIATE => 2,
        self::ADVANCED => 3,
        self::EXPERT => 4,
    ];

    private const LEVEL_LABELS = [
        self::BEGINNER => 'Начальный',
        self::INTERMEDIATE => 'Средний',
        self::ADVANCED => 'Продвинутый',
        self::EXPERT => 'Эксперт',
    ];

    private string $level;

    public function __construct(string|int $level)
    {
        if (is_int($level)) {
            $this->level = $this->getLevelFromInt($level);
        } else {
            if (!in_array($level, array_keys(self::LEVEL_VALUES), true)) {
                throw new DomainException('Invalid proficiency level');
            }
            $this->level = $level;
        }
    }

    private function getLevelFromInt(int $level): string
    {
        $flipped = array_flip(self::LEVEL_VALUES);
        if (!isset($flipped[$level])) {
            throw new DomainException('Invalid proficiency level value');
        }
        return $flipped[$level];
    }

    public function getValue(): int
    {
        return self::LEVEL_VALUES[$this->level];
    }

    public function getLabel(): string
    {
        return self::LEVEL_LABELS[$this->level];
    }

    public function equals(self $other): bool
    {
        return $this->level === $other->level;
    }

    public static function beginner(): self
    {
        return new self(self::BEGINNER);
    }

    public static function intermediate(): self
    {
        return new self(self::INTERMEDIATE);
    }

    public static function advanced(): self
    {
        return new self(self::ADVANCED);
    }

    public static function expert(): self
    {
        return new self(self::EXPERT);
    }

    public function toInt(): int
    {
        return self::LEVEL_VALUES[$this->level];
    }
}
