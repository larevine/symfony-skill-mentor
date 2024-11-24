<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class JWTTokenRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $username,
        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $password,
    ) {
    }

    public function getUser(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
