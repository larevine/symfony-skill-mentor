<?php

declare(strict_types=1);

namespace App\Domain\Dto\Message;

readonly class StudentGroupsMessage
{
    /**
     * @param array<int> $group_ids
     */
    public function __construct(
        private int $student_id,
        private array $group_ids,
    ) {
    }

    public function getStudentId(): int
    {
        return $this->student_id;
    }

    /**
     * @return array<int>
     */
    public function getGroupIds(): array
    {
        return $this->group_ids;
    }

    /**
     * @param array{student_id: int, group_ids: array<int>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['student_id'],
            $data['group_ids'],
        );
    }
}
