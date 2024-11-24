<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateGroupRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $teacher_id,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 100)]
        public int $min_students = 5,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 100)]
        public int $max_size = 30,
        /** @var array<array{skill_id: int, level: int}> */
        #[Assert\Type('array')]
        #[Assert\All([
            'constraints' => [
                new Assert\Collection([
                    'fields' => [
                        'skill_id' => [
                            new Assert\NotBlank(),
                            new Assert\Type('integer'),
                            new Assert\Positive(),
                        ],
                        'level' => [
                            new Assert\NotBlank(),
                            new Assert\Type('integer'),
                            new Assert\Range(min: 1, max: 5),
                        ],
                    ],
                    'allowExtraFields' => false,
                    'allowMissingFields' => false,
                ]),
            ],
        ])]
        public array $required_skills = [],
    ) {
    }
}
