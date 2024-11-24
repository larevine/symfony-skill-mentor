<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TeacherFilterRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public ?string $search = null,
        /** @var array<int>|null */
        #[Assert\Type('array')]
        #[Assert\All([
            'constraints' => [
                new Assert\Type('integer'),
                new Assert\Positive(),
            ],
        ])]
        public ?array $skill_ids = null,
        /** @var array<int>|null */
        #[Assert\Type('array')]
        #[Assert\All([
            'constraints' => [
                new Assert\Type('integer'),
                new Assert\Positive(),
            ],
        ])]
        public ?array $group_ids = null,
        #[Assert\Type('boolean')]
        public ?bool $available_for_groups = null,
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public int $page = 1,
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 100)]
        public int $per_page = 20,
        /** @var array<string>|null */
        #[Assert\Type('array')]
        #[Assert\All([
            'constraints' => [
                new Assert\Choice(['first_name', 'last_name', 'email']),
            ],
        ])]
        public ?array $sort_by = null,
        #[Assert\Choice(['asc', 'desc'])]
        public string $sort_order = 'asc',
    ) {
    }
}
