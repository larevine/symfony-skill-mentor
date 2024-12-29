<?php

declare(strict_types=1);

namespace App\Domain\Dto\Message;

class TeacherGroupsMessage
{
    /** @var array<int> */
    private array $group_ids;

    public function __construct(
        private readonly int $teacher_id,
        array $group_ids
    ) {
        $this->group_ids = array_map('intval', $group_ids);
    }

    public function getTeacherId(): int
    {
        return $this->teacher_id;
    }

    /** @return array<int> */
    public function getGroupIds(): array
    {
        return $this->group_ids;
    }

    public function toArray(): array
    {
        return [
            'teacher_id' => $this->teacher_id,
            'group_ids' => $this->group_ids,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            teacher_id: $data['teacher_id'],
            group_ids: $data['group_ids']
        );
    }
}
