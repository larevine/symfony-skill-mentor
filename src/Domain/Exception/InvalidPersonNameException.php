<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class InvalidPersonNameException extends BaseDomainException
{
    public static function empty(string $field): self
    {
        return new self(sprintf('%s cannot be empty', ucfirst($field)));
    }

    public static function tooShort(string $field, int $minLength): self
    {
        return new self(sprintf('%s must be at least %d characters long', ucfirst($field), $minLength));
    }

    public static function tooLong(string $field, int $maxLength): self
    {
        return new self(sprintf('%s cannot be longer than %d characters', ucfirst($field), $maxLength));
    }

    public static function invalidFormat(string $field): self
    {
        return new self(sprintf('%s can only contain letters, spaces and hyphens', ucfirst($field)));
    }
}
