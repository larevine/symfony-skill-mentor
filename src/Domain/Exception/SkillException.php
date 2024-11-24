<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class SkillException extends BaseDomainException
{
    public static function skillNotFound(int $id): self
    {
        return new self(sprintf('Skill with id %d not found', $id));
    }

    public static function skillAlreadyExists(string $name): self
    {
        return new self(sprintf('Skill with name %s already exists', $name));
    }

    public static function invalidSkillLevel(int $level, int $min, int $max): self
    {
        return new self(sprintf('Skill level must be between %d and %d, %d given', $min, $max, $level));
    }

    public static function fromDomainException(BaseDomainException $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
