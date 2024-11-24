<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class InvalidEntityIdException extends BaseDomainException
{
    public static function negative(int $id): self
    {
        return new self(sprintf('Entity ID cannot be negative: %d', $id));
    }

    public static function zero(): self
    {
        return new self('Entity ID cannot be zero');
    }
}
