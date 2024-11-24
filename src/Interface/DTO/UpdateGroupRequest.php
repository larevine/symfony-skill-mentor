<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateGroupRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public ?int $teacher_id = null,
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 100)]
        public ?int $max_students = null,
    ) {
    }
}
