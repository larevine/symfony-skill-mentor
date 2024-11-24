<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Статус пользователя
 */
enum UserStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public static function intValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toString(): string
    {
        return match ($this) {
            self::INACTIVE => 'INACTIVE',
            self::ACTIVE => 'ACTIVE',
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
            'INACTIVE' => self::INACTIVE,
            'ACTIVE' => self::ACTIVE,
        };
    }
}
