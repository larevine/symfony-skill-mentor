<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class ValueObjectException extends BaseDomainException
{
    public static function invalidId(int $id): self
    {
        return new self(sprintf('Invalid id: %d', $id));
    }

    public static function invalidEmail(string $email): self
    {
        return new self(sprintf('Invalid email: %s', $email));
    }

    public static function invalidName(string $name): self
    {
        return new self(sprintf('Invalid name: %s', $name));
    }

    public static function invalidSkillLevel(int $value): self
    {
        return new self(sprintf('Invalid skill level: %d. Must be between 1 and 5', $value));
    }
}
