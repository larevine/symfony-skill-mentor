<?php

declare(strict_types=1);

namespace App\Service\Security\Auth\Token;

use App\Manager\UserManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ApiAuthService implements AuthService
{
    public function __construct(
        private UserManager $user_manager,
        private UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function isCredentialsValid(string $email, string $password): bool
    {
        $user = $this->user_manager->findUserByEmail($email);
        if ($user === null) {
            return false;
        }

        return $this->password_hasher->isPasswordValid($user, $password);
    }

    public function getToken(string $email): ?string
    {
        return $this->user_manager->updateUserToken($email);
    }
}
