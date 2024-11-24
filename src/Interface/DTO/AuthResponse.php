<?php

declare(strict_types=1);

namespace App\Interface\DTO;

readonly class AuthResponse
{
    public function __construct(
        private string $token,
        private string $type = 'Bearer',
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'type' => $this->type,
        ];
    }

    public static function fromUserAndToken(string $token): self
    {
        return new self(
            token: $token,
            type: 'Bearer',
        );
    }
}
