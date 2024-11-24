<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Entity\Teacher;
use DomainException as BaseDomainException;

class TeacherException extends BaseDomainException
{
    public static function teacherNotFound(int $id): self
    {
        return new self(sprintf('Teacher with id %d not found', $id));
    }

    public static function teacherAlreadyExists(string $email): self
    {
        return new self(sprintf('Teacher with email %s already exists', $email));
    }

    public static function skillAlreadyExists(Teacher $teacher, int $skillId): self
    {
        return new self(sprintf('Teacher %d already has skill %d', $teacher->getId(), $skillId));
    }

    public static function skillNotFound(Teacher $teacher, int $skillId): self
    {
        return new self(sprintf('Teacher %d does not have skill %d', $teacher->getId(), $skillId));
    }

    public static function fromDomainException(BaseDomainException $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
