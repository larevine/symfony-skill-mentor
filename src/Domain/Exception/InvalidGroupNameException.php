<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class InvalidGroupNameException extends BaseDomainException
{
    public static function empty(): self
    {
        return new self('Group name cannot be empty');
    }

    public static function tooShort(int $minLength): self
    {
        return new self(sprintf('Group name must be at least %d characters long', $minLength));
    }

    public static function tooLong(int $maxLength): self
    {
        return new self(sprintf('Group name cannot be longer than %d characters', $maxLength));
    }

    public static function invalidFormat(): self
    {
        return new self('Group name can only contain letters, numbers, spaces and hyphens');
    }
}
