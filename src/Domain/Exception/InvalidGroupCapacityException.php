<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class InvalidGroupCapacityException extends BaseDomainException
{
    public static function negative(int $capacity): self
    {
        return new self(sprintf('Group capacity cannot be negative: %d', $capacity));
    }

    public static function zero(): self
    {
        return new self('Group capacity cannot be zero');
    }

    public static function tooLarge(int $capacity, int $maxCapacity): self
    {
        return new self(sprintf('Group capacity %d exceeds maximum allowed capacity of %d', $capacity, $maxCapacity));
    }
}
