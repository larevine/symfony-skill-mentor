<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateStudentRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $first_name,
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $last_name,
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public string $email,
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
        public array $skills = [],
    ) {
    }
}
