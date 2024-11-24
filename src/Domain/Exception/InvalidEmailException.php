<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class InvalidEmailException extends BaseDomainException
{
    public static function empty(): self
    {
        return new self('Email cannot be empty');
    }

    public static function invalid(string $email): self
    {
        return new self(sprintf('Invalid email format: %s', $email));
    }

    public static function tooLong(string $email, int $maxLength): self
    {
        return new self(sprintf('Email is too long: %s (max length is %d)', $email, $maxLength));
    }
}
