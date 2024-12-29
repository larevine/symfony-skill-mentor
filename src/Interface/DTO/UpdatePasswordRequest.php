<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdatePasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $current_password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $new_password,
    ) {
    }
}
