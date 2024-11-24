<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTeacherRequest
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
        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $password,
        #[Assert\NotBlank]
        #[Assert\GreaterThan(0)]
        public int $max_groups = 1,
    ) {
    }
}
