<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\EntityId;
use DomainException;

class StudentSkillException extends DomainException
{
    public static function skillNotFound(EntityId $skillId): self
    {
        return new self(sprintf('Skill with ID %d not found', $skillId->getValue()));
    }

    public static function studentNotFound(EntityId $studentId): self
    {
        return new self(sprintf('Student with ID %d not found', $studentId->getValue()));
    }
}
