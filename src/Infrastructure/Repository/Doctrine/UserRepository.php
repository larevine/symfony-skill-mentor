<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class UserRepository extends AbstractBaseRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }
}
