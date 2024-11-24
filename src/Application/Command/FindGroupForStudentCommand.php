<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\EntityId;

readonly class FindGroupForStudentCommand
{
    private EntityId $student_id;

    public function __construct(
        int $student_id
    ) {
        $this->student_id = new EntityId($student_id);
    }

    public function getStudentId(): int
    {
        return $this->student_id->getValue();
    }

    public function getStudentEntityId(): EntityId
    {
        return $this->student_id;
    }
}
