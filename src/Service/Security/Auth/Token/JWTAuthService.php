<?php

declare(strict_types=1);

namespace App\Service\Security\Auth\Token;

use App\Manager\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class JWTAuthService implements AuthService
{
    public function __construct(
        private UserManager $user_manager,
        private UserPasswordHasherInterface $password_hasher,
        private JWTEncoderInterface $jwt_encoder,
        private int $token_TTL,
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

    /**
     * @throws JWTEncodeFailureException
     */
    public function getToken(string $email): string
    {
        $user = $this->user_manager->findUserByEmail($email);
        $roles = $user ? $user->getRoles() : [];
        $tokenData = [
            'username' => $email,
            'roles' => $roles,
            'exp' => time() + $this->token_TTL,
        ];

        return $this->jwt_encoder->encode($tokenData);
    }
}
