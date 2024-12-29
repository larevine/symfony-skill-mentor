<?php

declare(strict_types=1);

namespace App\Domain\Dto\Message;

readonly class GroupSkillsMessage
{
    /**
     * @param array<array{id: int, level: int}> $skills
     */
    public function __construct(
        private int $group_id,
        private array $skills,
    ) {
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    /**
     * @return array<array{id: int, level: int}>
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param array{group_id: int, skills: array<array{id: int, level: int}>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['group_id'],
            $data['skills'],
        );
    }
}
