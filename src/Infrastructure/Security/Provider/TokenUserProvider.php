<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Provider;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

final readonly class TokenUserProvider
{
    public function __construct(
        private UserRepositoryInterface $user_repository
    ) {
    }

    public function findByToken(string $token): ?User
    {
        return $this->user_repository->findOneBy(['token' => $token]);
    }
}
