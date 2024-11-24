<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TeacherCreateRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $password,
        #[Assert\NotBlank]
        #[Assert\Length(min: 2)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Length(min: 2)]
        public string $surname,
        #[Assert\NotBlank]
        #[Assert\Range(min: 1, max: 10)]
        public int $maxGroups = 1,
        /** @var int[] */
        #[Assert\Type('array')]
        public array $skillIds = [],
    ) {
    }
}
