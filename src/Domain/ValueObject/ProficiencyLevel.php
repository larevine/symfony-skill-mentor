<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DomainException;

class ProficiencyLevel
{
    private const LEVELS = [
        'level_1' => ['value' => 1, 'label' => 'Начальный'],
        'level_2' => ['value' => 2, 'label' => 'Ниже среднего'],
        'level_3' => ['value' => 3, 'label' => 'Средний'],
        'level_4' => ['value' => 4, 'label' => 'Выше среднего'],
        'level_5' => ['value' => 5, 'label' => 'Продвинутый'],
    ];

    private string $level;

    public function __construct(string $level)
    {
        if (!isset(self::LEVELS[$level])) {
            throw new DomainException('Invalid proficiency level');
        }
        $this->level = $level;
    }

    public function getValue(): int
    {
        return self::LEVELS[$this->level]['value'];
    }

    public function getLabel(): string
    {
        return self::LEVELS[$this->level]['label'];
    }

    public function equals(self $other): bool
    {
        return $this->level === $other->level;
    }

    public static function fromInt(int $value): self
    {
        foreach (self::LEVELS as $key => $data) {
            if ($data['value'] === $value) {
                return new self($key);
            }
        }

        throw new DomainException('Invalid proficiency level value');
    }

    public static function values(): array
    {
        return array_column(self::LEVELS, 'value');
    }

    public static function labels(): array
    {
        return array_column(self::LEVELS, 'label');
    }
}
