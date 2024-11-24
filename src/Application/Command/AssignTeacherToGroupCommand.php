<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\EntityId;

readonly class AssignTeacherToGroupCommand
{
    private EntityId $group_id;
    private EntityId $teacher_id;

    public function __construct(
        int $group_id,
        int $teacher_id,
    ) {
        $this->group_id = new EntityId($group_id);
        $this->teacher_id = new EntityId($teacher_id);
    }

    public function getGroupId(): int
    {
        return $this->group_id->getValue();
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id->getValue();
    }

    public function getGroupEntityId(): EntityId
    {
        return $this->group_id;
    }

    public function getTeacherEntityId(): EntityId
    {
        return $this->teacher_id;
    }
}
