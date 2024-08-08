<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface IUserRepository
{
    public function findByEmail(string $email): ?User;
    public function findByToken(string $token): ?User;
    /**
     * @return User[]
     */
    public function findPaginated(int $page, int $per_page): array;
    public function save(User $user): void;
    public function delete(User $user): void;
}
