<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateTeacherRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public ?string $first_name = null,
        #[Assert\Length(min: 2, max: 255)]
        public ?string $last_name = null,
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public ?string $email = null,
        #[Assert\GreaterThan(0)]
        public ?int $max_groups = null,
    ) {
    }
}
