<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Exception\InvalidEntityIdException;
use App\Domain\Exception\ValueObjectException;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\SkillLevel;

readonly class AddStudentSkillCommand
{
    private EntityId $student_id;
    private EntityId $skill_id;
    private SkillLevel $level;

    /**
     * @throws ValueObjectException
     * @throws InvalidEntityIdException
     */
    public function __construct(
        int $student_id,
        int $skill_id,
        string $level,
    ) {
        $this->student_id = new EntityId($student_id);
        $this->skill_id = new EntityId($skill_id);
        $this->level = SkillLevel::fromInt((int)$level);
    }

    public function getStudentId(): int
    {
        return $this->student_id->getValue();
    }

    public function getSkillId(): int
    {
        return $this->skill_id->getValue();
    }

    public function getLevel(): SkillLevel
    {
        return $this->level;
    }

    public function getStudentEntityId(): EntityId
    {
        return $this->student_id;
    }

    public function getSkillEntityId(): EntityId
    {
        return $this->skill_id;
    }

    public function getSkillLevel(): SkillLevel
    {
        return $this->level;
    }
}
