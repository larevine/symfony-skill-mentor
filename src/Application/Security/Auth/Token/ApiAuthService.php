<?php

declare(strict_types=1);

namespace App\Application\Security\Auth\Token;

use App\Application\Interface\Service\IUserService;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ApiAuthService
{
    public function __construct(
        private IUserService $user_service,
        private UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function isCredentialsValid(string $email, string $password): bool
    {
        $user = $this->user_service->findUserByEmail($email);
        if ($user === null) {
            return false;
        }

        return $this->password_hasher->isPasswordValid($user, $password);
    }

    /**
     * @throws RandomException
     */
    public function getToken(string $email): ?string
    {
        return $this->user_service->updateUserToken($email);
    }
}
