<?php

declare(strict_types=1);

namespace App\Service\Security\Auth\Token;

interface AuthService
{
    public function isCredentialsValid(string $email, string $password): bool;
    public function getToken(string $email): ?string;
}
