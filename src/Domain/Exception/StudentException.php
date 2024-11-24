<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use DomainException;

class StudentException extends DomainException
{
    public static function fromDomainException(DomainException $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    public static function notInGroup(Student $student, Group $group): self
    {
        return new self(sprintf(
            'Student %s is not in group %s',
            $student->getFullName(),
            $group->getName()
        ));
    }

    public static function skillAlreadyExists(Student $student, string $skillName): self
    {
        return new self(sprintf(
            'Student %s already has skill %s',
            $student->getFullName(),
            $skillName
        ));
    }

    public static function skillNotFound(Student $student, string $skillName): self
    {
        return new self(sprintf(
            'Student %s does not have skill %s',
            $student->getFullName(),
            $skillName
        ));
    }
}
